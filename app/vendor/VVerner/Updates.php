<?php 

namespace VVerner;

use \WP_REST_Request;
use \WP_REST_Response;
use \ZipArchive;

defined('ABSPATH') || exit;

class Updates
{
    private const NAMESPACE     = '/vverner/update';
    private const UPDATE_TOKEN  = '4iUPW1O4pgbe';
    private const TEMPORARY_DIR = WP_CONTENT_DIR . '/vverner-temp';

    public static function registerRoutes(): void
    {
        $self = new self();
        $self->addRoute('plugins', 'handlePluginUpdate');
        $self->addRoute('themes', 'handleThemeUpdate');
    }

    private function addRoute(string $route, string $cb): void
    {
        register_rest_route( self::NAMESPACE, '/' . $route, [
            'methods'             => 'POST',
            'callback'            => [$this, $cb],
            'permission_callback' => [$this, 'verifyUpdateToken'],
        ]);
    }

    public function verifyUpdateToken( WP_REST_Request $request ): bool
    {
        error_log('request!');
        return $request->get_header('vverner-token') === self::UPDATE_TOKEN;
    }

    public function handlePluginUpdate( WP_REST_Request $request ): WP_REST_Response
    {
        $body = $request->get_json_params();

        if ($body) :
            $this->login();

            if (!function_exists('activate_plugin')) :
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            endif;

            array_map([$this, 'installPlugin'], $body['items']);
        endif;

        return new WP_REST_Response();
    }

    public function handleThemeUpdate( WP_REST_Request $request ): WP_REST_Response
    {
        $body = $request->get_json_params();

        if ($body) :
            $this->login();

            if (!function_exists('activate_plugin')) :
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            endif;

            array_map([$this, 'installTheme'], $body['items']);
        endif;

        return new WP_REST_Response();
    }

    private function installPlugin( array $plugin ): void
    {
        $request  = new WP_REST_Request('GET', '/wp/v2/plugins/' . $plugin['id']);
        $response = rest_do_request($request)->data;

        if (isset($response['version']) && version_compare( $plugin['version'], $response['version'], '>')) :
            $this->createTemporaryDir();
            $copied = $this->copyZipFile( $plugin['file'] );

            if ($copied)  : 
                $this->setUpdateDir();
                $this->movePluginFiles();
            endif;

            $this->removeTemporaryDir();
        endif;
    }

    private function installTheme( array $theme ): void
    {
        $request         = new WP_REST_Request('GET', '/wp/v2/themes');
        $response        = rest_do_request($request)->data;
        $foundTheme      = null;

        foreach ($response as $responseTheme) : 
            if ($responseTheme['template'] === $theme['id']) :
                $foundTheme = $responseTheme;
                break;
            endif;
        endforeach;

        if ($foundTheme && version_compare( $theme['version'], $foundTheme['version'], '>')) :
            $this->createTemporaryDir();

            $copied = $this->copyZipFile( $theme['file'] );

            if ($copied)  : 
                $this->setUpdateDir();
                $this->moveThemeFiles();
            endif;

            $this->removeTemporaryDir();
        endif;
    }

    private function createTemporaryDir(): void
    {
        if (!is_dir(self::TEMPORARY_DIR)) : 
            mkdir(self::TEMPORARY_DIR);
        endif;
    }

    private function copyZipFile(string $source): bool
    {
        global $wp_filesystem;

        if (!$wp_filesystem) : 
            require_once ABSPATH . '/wp-admin/includes/file.php';
            WP_Filesystem();
        endif;

        $pluginRequest = wp_remote_get($source, ['sslverify' => false]);
        $zipContent    = wp_remote_retrieve_body( $pluginRequest );
        $zipPath       = self::TEMPORARY_DIR . '/plugin.zip';

        $wp_filesystem->put_contents($zipPath, $zipContent);

        $worker = new ZipArchive;
        $res    = $worker->open( $zipPath );
        
        if ($res !== true) :
            unlink( $zipPath );
            return false;
        endif;

        $worker->extractTo( self::TEMPORARY_DIR );
        $worker->close();

        unlink( $zipPath );

        return true;
    }

    private function movePluginFiles(): bool
    {
        if (!$this->installedDir) : 
            return false;
        endif;

        if ( is_dir( $this->getPluginInstallationDir() ) ) : 
            $this->removeDirCompletely( $this->getPluginInstallationDir() );
        endif;

        return rename( 
            self::TEMPORARY_DIR . '/' . $this->installedDir, 
            $this->getPluginInstallationDir()
        );
    }

    private function moveThemeFiles(): bool
    {
        if (!$this->installedDir) : 
            return false;
        endif;

        if ( is_dir( $this->getThemeInstallationDir() ) ) : 
            $this->removeDirCompletely( $this->getThemeInstallationDir() );
        endif;

        return rename( 
            self::TEMPORARY_DIR . '/' . $this->installedDir, 
            $this->getThemeInstallationDir()
        );
    }

    private function setUpdateDir(): void
    {
        $scan = scandir( self::TEMPORARY_DIR );
        $scan = array_diff($scan, ['.', '..', '__MACOSX']);
        $this->installedDir = array_pop($scan);
    }

    private function removeTemporaryDir(): void
    {
        $this->removeDirCompletely( self::TEMPORARY_DIR );
    }

    private function getPluginInstallationDir(): string
    {
        return WP_CONTENT_DIR  . '/plugins/' . $this->installedDir;
    }

    private function getThemeInstallationDir(): string
    {
        return WP_CONTENT_DIR . '/themes/' . $this->installedDir;
    }

    private function removeDirCompletely(string $path): void
    {
        $files = glob($path . '/*');

        foreach ($files as $file) :
            is_dir($file) ? $this->removeDirCompletely($file) : unlink($file);
        endforeach;

        rmdir($path);
    }

    private function login(): void
    {
        $users = get_users([
            'role' => 'administrator'
        ]);
        
        $admin = $users[0];
        wp_clear_auth_cookie();
        wp_set_current_user ( $admin->ID );
        wp_set_auth_cookie  ( $admin->ID );
    }
}
 
add_action('rest_api_init', function(){
    Updates::registerRoutes();
});
