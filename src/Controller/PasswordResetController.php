<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\DBAL\DriverManager;

class PasswordResetController extends AbstractController
{
    public function forgotPassword(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            return $this->handleForgotPassword($request);
        }
        
        return $this->render('password_reset/forgot.html.twig', [
            'error' => null,
            'rut_empresa' => '',
            'rut_usuario' => '',
            'email' => ''
        ]);
    }
    
    private function handleForgotPassword(Request $request): Response
    {
        $rutEmpresa = $request->request->get('rut_empresa');
        $rutUsuario = $request->request->get('rut_usuario');
        $email = $request->request->get('email');
        
        if (!$rutEmpresa || !$rutUsuario || !$email) {
            return $this->render('password_reset/forgot.html.twig', [
                'error' => 'Todos los campos son requeridos',
                'rut_empresa' => $rutEmpresa,
                'rut_usuario' => $rutUsuario,
                'email' => $email
            ]);
        }
        
        // Conectar a la base de datos central
        $connectionParams = [
            'host' => 'localhost',
            'port' => 5432,
            'dbname' => $_ENV['CENTRAL_DB_NAME'],
            'user' => $_ENV['CENTRAL_DB_USER'],
            'password' => $_ENV['CENTRAL_DB_PASSWORD'],
            'driver' => 'pdo_pgsql',
        ];
        
        try {
            $connection = DriverManager::getConnection($connectionParams);
            
            // Verificar que el usuario existe y pertenece al tenant
            $userQuery = '
                SELECT m.id, m.email, m.rut, m.first_name, m.last_name, t.name as tenant_name, t.rut_empresa
                FROM member m
                INNER JOIN tenant_member tm ON m.id = tm.member_id
                INNER JOIN tenant t ON tm.tenant_id = t.id
                WHERE m.rut = ? AND t.rut_empresa = ? AND m.email = ? 
                AND m.is_active = true AND tm.is_active = true AND t.is_active = true
            ';
            $userResult = $connection->executeQuery($userQuery, [$rutUsuario, $rutEmpresa, $email]);
            $user = $userResult->fetchAssociative();
            
            if (!$user) {
                return $this->render('password_reset/forgot.html.twig', [
                    'error' => 'No se encontró un usuario con esos datos. Verifique RUT de empresa, RUT de usuario y email.',
                    'rut_empresa' => $rutEmpresa,
                    'rut_usuario' => $rutUsuario,
                    'email' => $email
                ]);
            }
            
            // Generar token de recuperación
            $resetToken = bin2hex(random_bytes(32));
            $expiry = new \DateTime('+1 hour');
            
            // Guardar token en base de datos (para demo, solo mostramos el token)
            // En producción deberías crear una tabla password_reset_tokens
            
            return $this->render('password_reset/token_sent.html.twig', [
                'user' => $user,
                'reset_token' => $resetToken,
                'reset_url' => $this->generateUrl('app_reset_password', ['token' => $resetToken])
            ]);
            
        } catch (\Exception $e) {
            return $this->render('password_reset/forgot.html.twig', [
                'error' => 'Error de conexión: ' . $e->getMessage(),
                'rut_empresa' => $rutEmpresa,
                'rut_usuario' => $rutUsuario,
                'email' => $email
            ]);
        }
    }
    
    public function resetPassword(Request $request, string $token): Response
    {
        if ($request->isMethod('POST')) {
            return $this->handleResetPassword($request, $token);
        }
        
        return $this->render('password_reset/reset.html.twig', [
            'token' => $token,
            'error' => null
        ]);
    }
    
    private function handleResetPassword(Request $request, string $token): Response
    {
        $newPassword = $request->request->get('password');
        $confirmPassword = $request->request->get('confirm_password');
        
        if (!$newPassword || !$confirmPassword) {
            return $this->render('password_reset/reset.html.twig', [
                'error' => 'Todos los campos son requeridos',
                'token' => $token
            ]);
        }
        
        if ($newPassword !== $confirmPassword) {
            return $this->render('password_reset/reset.html.twig', [
                'error' => 'Las contraseñas no coinciden',
                'token' => $token
            ]);
        }
        
        if (strlen($newPassword) < 6) {
            return $this->render('password_reset/reset.html.twig', [
                'error' => 'La contraseña debe tener al menos 6 caracteres',
                'token' => $token
            ]);
        }
        
        // Para demo, simplemente mostramos éxito
        // En producción deberías validar el token y actualizar la contraseña
        
        return $this->render('password_reset/success.html.twig', [
            'message' => 'Contraseña actualizada exitosamente. Para testing, la nueva contraseña sigue siendo: 123456'
        ]);
    }
}