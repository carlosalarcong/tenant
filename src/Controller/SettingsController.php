<?php

namespace App\Controller;

use App\Entity\Tenant\Setting;
use App\Repository\Tenant\SettingRepository;
use App\Service\Settings;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;


#[Route('/settings')]
class SettingsController extends AbstractTenantAwareController
{
    private array $formData = [];

    public function __construct(
        private readonly Settings $settings,
        private readonly SettingRepository $settingRepository,
        private readonly Connection $connection,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    )
    {
    }

    #[Route('/', name: 'app_settings')]
    public function index($parent = 'global'): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $setting = $this->settings->getBySlug('instance_language');

        try {
            $config = Yaml::parseFile($this->projectDir . '/config/settings.yml');

            if ($config === null) {
                $config = array();
            }
            if (!is_array($config)) {
                throw new \Exception("Archivo de settings inválido.");
                exit;
            }
        } catch (ParseException $e) {
            throw new \Exception("Se ha producido un error al parsear los settings.");
            exit;
        }

        $slug_ = $parent;
        $parent = $this->settingRepository->findBy(['slug' => $slug_]);
        $tmp_parent = null;
        if ($parent == null) {
            foreach ($config as $pkey => $pval) {
                if ($pkey == $slug_) {
                    $tmp_parent = $pval;
                    break;
                }
            }
            $parent = new Setting();
            $parent->setName($pval['name']);
            $parent->setSlug($slug_);
            $parent->setDescription($pval['description']);
        }

        return $this->render('default/settings/index.html.twig', [
            'config' => $config,
            'parent' => $parent,
        ]);
    }

    #[Route('/{parent}/more', name: 'app_settings_more', defaults: ['parent' => 'global'])]
    public function more($parent = 'global', $setting_slug = null): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        try {
            $config = Yaml::parseFile($this->projectDir . '/config/setting.yml');

            if ($config === null) {
                $config = array();
            }
            if (!is_array($config)) {
                throw new \Exception("Archivo de settings inválido.");
                exit;
            }
        } catch (ParseException $e) {
            throw new \Exception("Se ha producido un error al parsear los settings.");
            exit;
        }


        $slug_ = $parent; //keep reference slug to parent
        $current_level = 0; //keep parent depth level

        $settings = $this->settingRepository->getAllSetting();

        if (!is_null($setting_slug)) {
            $stmp = $this->settings->getSubyamlArray($config, $setting_slug);
            $setting = $stmp['values'];
            $current_level = $stmp['depth'];
            $root = $stmp['root'];
            $parent = $this->settingRepository->findOneBy(['slug' => $root]);
            $slug_ = $parent->getSlug(); //update the parent if we want be added to form!!!

        } else {
            $parent = $this->settingRepository->findOneBy(['slug' => $slug_]);
            $tmp_parent = null;
            if ($parent == null) {
                foreach ($config as $pkey => $pval) {
                    if ($pkey == $slug_) {
                        $tmp_parent = $pval;
                        break;
                    }
                }
                $parent = new Setting();
                $parent->setName($pval['name']);
                $parent->setSlug($slug_);
                $parent->setDescription($pval['description']);
            }

            $setting = array_key_exists('children', $config[$slug_]) ? $setting = $config[$slug_] : array();
        }

        $hashed_settings = $this->settings->getAllSettings(); //hashtable settings by slug

        /************ eof query settings *******/

        $form = $this->createFormBuilder(array(''));
        $this->formData = array();
        /*** iterate yaml ***/
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveArrayIterator($config),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $finder_options = array(); //searcher
        $parent_category = null;
        $parent_slug = null;
        $last_parent = null;

        //iterate tree yaml. this is specific tree yaml iteration for this particular view
        foreach ($iterator as $key => $val) {

            $slug = $key;
            if ($iterator->getDepth() == 0) {
                $parent_category = $val['name'];
                $parent_slug = $slug; //master parent
            }
            if (gettype($val) == "array" && $key != "children") {
                $show_in_front = true;
                if (array_key_exists('available_in_frontend', $val)) {
                    if ($val['available_in_frontend'] == 0) $show_in_front = false;
                }
                //this is for searcher info
                if ($show_in_front && array_key_exists('name', $val)) {
                    if ($last_parent != null && $iterator->getDepth() > 1) {
                        //$name = $last_parent . " > " . $this->get('trans')->trans($val['name']);
                        $name = $last_parent . " > " . $val['name'];
                    } else {
                        //$name = $this->get('trans')->trans($val['name']);
                        $name = $val['name'];
                    }

                    if (!is_int($slug)) {
                        $finder_options[] = array(
                            'setting_id' => $slug,
                            'label' => $name,
                            //'category' => $this->get('trans')->trans($parent_category)
                            'category' => $parent_category
                        );
                    }

                }
                //we need add to form the childs of parent slug
                if ($show_in_front && $parent_slug == $slug_) {
                    $is_widget = array_key_exists('field_format', $val);
                    if ($is_widget) {

                        $this->add_to_form($form, $key, $val, $hashed_settings);
                    }
                }
                if (array_key_exists('children', $val) && $show_in_front) {
                    //$last_depth = $iterator->getDepth();
                    //$last_parent = $this->get('trans')->trans($val['name']);
                    $last_parent = $val['name'];
                }

            }

        }

        $form->setData($this->formData);
        $form = $form->getForm();

        $a = array(
            'form' => $form->createView(),
            'parent' => $parent,
            'hashed_settings' => $hashed_settings,
            'finder_options' => $finder_options,
            'config' => $config,
            'curr_config' => $setting,
            'current_level' => $current_level,
            'setting_slug' => $setting_slug,
        );

        return $this->render('default/settings/more.html.twig', $a);
    }

    private function add_to_form($form, $key, $item, $hashed_settings, $for_wizard = false)
    {
        if ($item['available_in_frontend'] != 1 && !$for_wizard) return;

        $params = array('required' => false, 'label' => $item['name']);
        $newParams = array();
        $format = $item['field_format'];

        $fieldValue = $item['field_default_value']; //temp value
        if (gettype($fieldValue) == "array") $fieldValue = serialize($fieldValue); // Serializing all array values coming from yml format


        if (array_key_exists($key, $hashed_settings)) {
            //var_dump($hashed_settings['manual_redirect_link']);
            if (array_key_exists('field_value', $hashed_settings[$key]))
                $set_value = $hashed_settings[$key]['field_value'];
            else
                $set_value = $hashed_settings[$key]['field_default_value'];

            //if (gettype($set_value) == "array") $set_value = serialize($set_value);

            $fieldValue = $set_value;
            //if (!is_null($set_value) && trim($set_value)) $fieldValue = $set_value;
        }


        $specialAttributes = array();

        if (array_key_exists('special_attributes', $item)) {
            if ($this->isSerialized($item['special_attributes'])) {
                $specialAttributes = unserialize($item['special_attributes']);
            } else {
                $specialAttributes = $item['special_attributes'];
            }
        }


        //field required?
        $field_required = false;
        if (array_key_exists('field_required', $item)) {
            $field_required = (bool)$item['field_required'];
        }

        switch ($format) {
            case "integer":
                $format = IntegerType::class;
                $newParams = array(
                    'required' => $field_required,
                    'attr' => ['class' => 'form-control'],
                    'label_attr' => ['class' => 'col-md-3 form-label']
                );
                break;

            case "string":
                $format = 'text';
                $newParams = array(
                    'required' => $field_required,
                    'attr' => ['class' => 'form-control'],
                    'label_attr' => ['class' => 'col-md-3 form-label']
                );
                break;

            case 'choice':
                $format = ChoiceType::class;
                if (isset($specialAttributes["type"]) && $specialAttributes["type"] == "custom-sql") {
                    $result = $this->connection->fetchAllAssociative($specialAttributes["sql"]);
                    if (isset($specialAttributes["default_selection_text"])) {
                        $choices = array('' => $specialAttributes["default_selection_text"]);
                    } else {
                        $choices = array('' => 'Select an option');
                    }

                    foreach ($result as $row) {
                        $choices[$row[$specialAttributes["field_value"]]] = $row[$specialAttributes["field_text"]];
                    }

                    $newParams = array(
                        'choices' => $choices,
                        'required' => $field_required ? true : false
                    );
                } else {
                    if (gettype($item['field_available_value']) != "array") {
                        $newParams = array(
                            'choices' => unserialize($item['field_available_value']),
                            'required' => $field_required,
                            'attr' => ['class' => 'form-control'],
                            'label_attr' => ['class' => 'col-md-3 form-label']
                        );
                    } else {
                        $choices = array();
                        foreach ($item['field_available_value'] as $k => $v) {
                            $choices[$k] = $v;
                        }
                        $newParams = array(
                            'choices' => $choices,
                            'required' => $field_required,
                            'attr' => ['class' => 'form-control'],
                            'label_attr' => ['class' => 'col-md-3 form-label']
                        );
                    }

                }
                $fieldValue = $fieldValue;

                break;

            case "boolean":

                $format = CheckboxType::class;
                $fieldValue = (boolean)$fieldValue;
                $newParams = array(
                    'required' => $field_required,
                    'label_attr' => ['class' => 'col-md-3 form-label']
                );
                break;

            case 'language':
                $newParams = array(
                    'preferred_choices' => array('es', 'en'),
                    'required' => $field_required
                );
                break;

            case 'logo':
                $newParams = array(
                    'required' => $field_required,
                    'data_class' => null,
                    'attr' => array(
                        'class' => 'logo',
                        'data-image' => $this->generateUrl(
                            'app_settings_image',
                            array('slug' => $key)
                        ),
                    )
                );
                $format = 'file';
                break;

            case 'checkbox':

                dd('checkbox');
                $fieldValue = (boolean)$fieldValue;
                $newParams = array(
                    'required' => $field_required
                );
                break;

            case 'array':
                dd('array');
                //TODO: remove pesky little [0] index
                if (gettype($fieldValue) != "array") {
                    if ($this->isSerialized($fieldValue)) {
                        $fieldValue = unserialize(trim($fieldValue));
                    } else {
                        if (gettype($item['field_default_value']) == "array") {
                            $fieldValue = $item['field_default_value'];
                        } else {
                            $fieldValue = unserialize($item['field_default_value']);
                        }
                    }
                } else {
                    if ($this->isSerialized($fieldValue)) {
                        $fieldValue = unserialize($item['field_default_value']);
                    } else {
                        $fieldValue = $item['field_default_value'];
                    }
                }

                if (!$fieldValue) {
                    $fieldValue[] = array(
                        'key' => '',
                        'value' => ''
                    );
                }


                $format = 'collection';
                $newParams = array(
                    'allow_add' => true,
                    'allow_delete' => true,
                    'type' => $type,
                );

                if (isset($fieldValue[0][0])) {
                    $fieldValue_tmp[0] = array();
                    foreach ($fieldValue[0] as $field) {
                        $keyField = array_keys($field);
                        $keyField = $keyField[0];
                        if ($field['type'] == 'checkbox') {
                            $fieldValue_tmp[0][$keyField] = (boolean)$field[$keyField];
                        } elseif ($field['type'] == 'number') {
                            $fieldValue_tmp[0][$keyField] = (int)$field[$keyField];
                        } else {
                            $fieldValue_tmp[0][$keyField] = (string)$field[$keyField];
                        }
                    }

                    $fieldValue = $fieldValue_tmp;
                }

                break;

            case 'date':
                dd('date');
                if (trim($fieldValue) != '') {
                    $fieldValue = new \DateTime($fieldValue);
                }
                break;
        }

        $params = array_merge($params, $newParams);

        $form->add($key, $format, $params);

        $this->formData[$key] = $fieldValue;
    }

    #[Route('/image/{slug}', name: 'app_settings_image')]
    public function imageAction($slug)
    {
        dd('app_settings_image');
        $setting_value = $this->settings->getBySlug($slug);
        if (empty($setting_value) || !file_exists($setting_value)) {
            $imgHash = "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGP6zwAAAgcBApocMXEAAAAASUVORK5CYII=";
            $img = imagecreatefromstring(base64_decode($imgHash));
            header('Content-Type: image/png');
            imagepng($img);
            exit;
        } else {
            $info = getimagesize($setting_value);
            $response = new Response(file_get_contents($setting_value));
            $response->headers->set('Content-type', $info['mime']);
            $response->setCache(
                array(
                    'etag' => 'abcdef',
                    'last_modified' => new \DateTime(),
                    'max_age' => 600,
                    's_maxage' => 600,
                    'private' => false,
                    'public' => true,
                )
            );

            return $response;
        }
    }

    /**
     * Helper method to check if a string is serialized
     */
    private function isSerialized($data): bool
    {
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ($data === 'N;') {
            return true;
        }
        if (strlen($data) < 4) {
            return false;
        }
        if ($data[1] !== ':') {
            return false;
        }
        $lastChar = substr($data, -1);
        if ($lastChar !== ';' && $lastChar !== '}') {
            return false;
        }
        $token = $data[0];
        switch ($token) {
            case 's':
                if ($data[strlen($data) - 2] !== '"') {
                    return false;
                }
            case 'a':
            case 'O':
                return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b':
            case 'i':
            case 'd':
                return (bool) preg_match("/^{$token}:[0-9.E-]+;$/", $data);
        }
        return false;
    }
}