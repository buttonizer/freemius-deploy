<?php
    // run as: php ./deploy.php $file_name $version $sandbox $release_mode
    // with env vars: USER_ID, PUBLIC_KEY, SECRET_KEY, PLUGIN_SLUG
    echo "\n- Deploying " . str($_ENV['PLUGIN_SLUG']) . " to Freemius";

    require_once 'freemius-php-api/freemius/FreemiusBase.php';
    require_once 'freemius-php-api/freemius/Freemius.php';
	$sandbox      = ( $argv[3] === 'true' );
	$release_mode = ! isset( $argv[4] ) || empty( $argv[4] ) ? 'pending' :  $argv[4];
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
        if ( $deploy->tags[0]->version === $argv[2] ) {
                $deploy = $deploy->tags[0];
                echo '-Package already deployed on Freemius'."\n";
        } else {
            // Upload the zip
            $deploy = $api->Api('plugins/'.$_ENV['PLUGIN_SLUG'].'/tags.json', 'POST', array(
                'add_contributor' => false
            ), array(
                'file' => $argv[1]
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

        $path = pathinfo($argv[1]);
        $newzipname = $path['dirname'] . '/' . basename($argv[1], '.zip');
        $newzipname .= '__free.zip';

        file_put_contents($newzipname,file_get_contents($zip));
        
        echo "- Downloaded Freemius free version\n";
    }
    catch (Exception $e) {
        echo "- Freemius server has problems\n";
    }