// HOOKS DO TEMA
// ========================================
add_action('vv_assets-{POST_KEY}');
   - wp_enqueue_style();
   - wp_enqueue_script();
   - wp_localize_script();
add_action('vv_{POST_TYPE}_updated', function(WP_POST $postSendoAtualizado, array $dadosEnviadosPeloUsuario){}, 10, 2);
add_action('vv_{POST_TYPE}_deleted', function(WP_POST $postSendoApagado){});

add_filter('vv_api-{ROTA}', function(array $respostaParaRequest, array $dadosEnviadosPeloUsuario){}, 10, 2);
add_filter('vv_post_type_args-{POST_TYPE}', function(array $argumentosParaRegistroDoPostType){});
add_filter('vv_shortcode-{SHORTCODE}', function(array $dadosParaUsoNaView){});
add_filter('vv_taxonomy_args-{TAXONOMY}', function(array $argumentosParaRegistroDaTaxonomia){});


// SHORTCODES
// ========================================
$shortcodes = VVerner\Shortcodes::getInstance();
$shortcodes->add('teste', [
   'lorem'  => 123
]);

add_filter('vv_shortcode-teste', function($args){
   return ['lorem' => 456];
});


// POST TYPES
// ========================================
$cpt = new VVerner\PostType('Carro', 'Carros', 'car');
$cpt->setIcon('dashicons-car');
$cpt->setSupports(['title', 'thumbnail', 'editor']);
$cpt->setPublic(true);
$cpt->addMetaBox('Dados do veículo');
$cpt->register();

add_action('vv_car_updated', function(){
   VVerner\App::getInstance()->log('howdu');
});

add_action('vv_car_deleted', function(){
   VVerner\App::getInstance()->log('ops');
});


// TAXONOMIES
// ========================================
$tax = new VVerner\Taxonomy('Marca', 'Marcas', 'brand');
$tax->setPostType('car');
$tax->setPublic(false);
$tax->register();


// AJAX
// =========================================
$ajax = VVerner\AjaxAPI::getInstance();
$ajax->registerPrivateRoute('teste_nome');
$ajax->registerPublicRoute('teste_nome');

add_filter('vv_api-teste_nome', function(array $response, array $request){
   VVerner\App::getInstance()->log($response);
   VVerner\App::getInstance()->log($request);

   return $response;
}, 10, 2);

// ASSETS
// ========================================
$assets = VVerner\Assets::getInstance(); 
$assets->registerCss('main');
$assets->registerJs('app');

$assets->localizeJs('app', [
   'sec'    => VVerner\AjaxAPI::getInstance()->getGlobalNonce(),
   'action' => VVerner\AjaxAPI::getInstance()->getGlobalAction(),
   'url'    => VVerner\AjaxAPI::getInstance()->getRequestUrl()
]);