<?php
    // run as: php /deploy.php $file_name $version $sandbox $release_mode
    // with env vars: USER_ID, PUBLIC_KEY, SECRET_KEY, PLUGIN_SLUG
    $file_name = $_ENV['INPUT_FILE_NAME'];
    $version = $_ENV['INPUT_VERSION'];
    $sandbox = ($_ENV['INPUT_SANDBOX'] === 'true' );
    $release_mode = ! isset( $_ENV['INPUT_RELEASEMODE'] ) || empty( $_ENV['INPUT_RELEASEMODE'] ) ? 'pending' :  $_ENV['INPUT_RELEASEMODE'];

    echo "\n- Deploying " . $_ENV['PLUGIN_SLUG'] . " to Freemius, with arguments: ";
    echo "\n- file_name: " . $file_name . " version: " . $version . " sandbox: " . $sandbox . " release_mode: " . $release_mode;

    require_once 'freemius-php-api/freemius/FreemiusBase.php';
    require_once 'freemius-php-api/freemius/Freemius.php';
	define( 'FS__API_SCOPE', 'developer' );
	define( 'FS__API_DEV_ID', $_ENV['DEV_ID'] );
	define( 'FS__API_PUBLIC_KEY', $_ENV['PUBLIC_KEY'] );
	define( 'FS__API_SECRET_KEY', $_ENV['SECRET_KEY'] );

    echo "\n- Deploy in progress on Freemius\n";

    try {
        // Init SDK.
        $api = new Freemius_Api(FS__API_SCOPE, FS__API_DEV_ID, FS__API_PUBLIC_KEY, FS__API_SECRET_KEY, $sandbox);

        if (!is_object($api)) {
            print_r($deploy);
            die();
        }

        $deploy = $api->Api('plugins/'.$_ENV['PLUGIN_SLUG'].'/tags.json');
        if ( $deploy->tags[0]->version === $version ) {
                $deploy = $deploy->tags[0];
                echo '-Package already deployed on Freemius'."\n";
        } else {
            // Upload the zip
            $deploy = $api->Api('plugins/'.$_ENV['PLUGIN_SLUG'].'/tags.json', 'POST', array(
                'add_contributor' => false
            ), array(
                'file' => $file_name
            ));

            if (!property_exists($deploy, 'id')) {
                print_r($deploy);
                die();
            }

            echo "- Deploy done on Freemius\n";

            $is_released = $api->Api('plugins/'.$_ENV['PLUGIN_SLUG'].'/tags/'.$deploy->id.'.json', 'PUT', array(
                'release_mode' => $release_mode
            ), array());

            echo "- Set as released on Freemius\n";
        }

        echo "- Download Freemius free version\n";
        
        // Generate url to download the zip
        $zip = $api->GetSignedUrl('plugins/'.$_ENV['PLUGIN_SLUG'].'/tags/'.$deploy->id.'.zip');

        $path = pathinfo($file_name);
        $newzipname = $path['dirname'] . '/' . basename($file_name, '.zip');
        $newzipname .= '__free.zip';

        file_put_contents($newzipname,file_get_contents($zip));
        
        echo "- Downloaded Freemius free version\n";
    }
    catch (Exception $e) {
        echo "- Freemius server has problems\n";
    }