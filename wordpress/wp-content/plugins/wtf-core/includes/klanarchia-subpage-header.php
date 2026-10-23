<?php
if (!defined('ABSPATH')) exit;

// Starsze bloki pozostają rozpoznawane; nawigacja jest w nagłówku głównym.
function wtf_core_render_klan_subpage_header(){ return ''; }

add_action('init',function(){
    foreach(['wtf/klan-subpage-header','wtf/klan-back'] as $name)register_block_type($name,['api_version'=>3,'render_callback'=>'wtf_core_render_klan_subpage_header']);
});
