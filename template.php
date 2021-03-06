<?php
/**
 * Override or insert variables into the page templates.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("page" in this case.)
 */

function blendedmalts_preprocess_page(&$vars, $hook) {
    $currenttype = $vars['node']->type;
    if($currenttype=="individual") {
        blendedmalts_viewadjust($vars, "page");
        blendedmalts_removetab('/edit', $vars);
        blendedmalts_changetab('/tedit', $vars);
        blendedmalts_removetab('/tview', $vars);
        blendedmalts_removetab('/xview', $vars);
                
    }
}
 
/**
 * Adding additional preprocess functions inside existing preprocess functions
 * http://drupal.org/node/337022
 */
 function blendedmalts_preprocess_node(&$vars, $hook) {
  $function = 'blendedmalts_preprocess_node'.'_'. $vars['node']->type;
  if (function_exists($function)) {
   $function($vars, $hook);
  }
}

/**
 * Adding additional variables for node-individual
 */
function blendedmalts_preprocess_node_individual(&$vars, $hook) {
    blendedmalts_viewadjust($vars, "node");   
//    blendedmalts_removetab('/edit', $vars);
//    blendedmalts_removetab('/tview', $vars);
//    blendedmalts_removetab('/xview', $vars);
}

function blendedmalts_viewadjust(&$vars, $mode = "node") {
  // Get the node object

  $node = &$vars['node'];

  module_load_include('inc', 'wisski_pathbuilder');
  
  if(!empty($node) && !empty($node->title))
    $groupid = wisski_pathbuilder_getGroupIDForIndividual(wisski_store_getObj()->wisski_ARCAdapter_delNamespace($node->title));

  if(!$groupid || !isset($groupid))
    return;
  
  if($mode == "page") {
    $vars['maltedtitle'] = wisski_pathbuilder_generateGroupName($node->title, $groupid);
  } else if($mode == "node") {
    
    // this relies on the output of wisski_view() char by char
    // so first see there or in alter hooks if something broke
    
    if (strpos($vars['content'], '<div id="wki-content-other"></div>') !== FALSE) {
      
      // this is for the newest wisski_view() version (39ee47a0ef)
      // we wrap the div wki-content-other around all other/following content
      // (e.g. file attachments)
      $vars['maltedcontent'] = str_replace('<div id="wki-content-other"></div>', '<div id="wki-content-other">', $vars['content']) . '</div>';

    } elseif (strpos($vars['content'], '<div id="wki-content-right">') === FALSE) {
      
      // this is for the very old wisski_view() (fbb0f44f66) which effectively
      // does nothing. construct the wisski content in the old fashion and add
      // further content.
      $block2 = module_invoke('wisski_pathbuilder', 'block', 'view', 0);
      $block1 = module_invoke('wisski_pathbuilder', 'block', 'view', 1);
      $vars['maltedcontent'] = '<div id="wki-content-right">' . $block2['content'] . '</div>' . '<div id="wki-content-left">' . $block1['content'] .  '</div><div id="wki-content-other">' . $vars['content'] . '</div>';
    
    }

    // for the version in between we do nothing and let the default theming put
    // the content together

  }              

}

function blendedmalts_removetab($label, &$vars) {
  $tabs = explode("\n", $vars['tabs']);
  $vars['tabs'] = '';
    
  foreach ($tabs as $tab) {
    if (strpos($tab, '' . $label . '">') === FALSE) {
      $vars['tabs'] .= $tab . "\n";
      
    }
  }
}

function blendedmalts_changetab($label, &$vars) {
  $tabs = explode("\n", $vars['tabs']);
  $vars['tabs'] = '';
    
  foreach ($tabs as $tab) {
    if (strpos($tab, '' . $label . '">') === FALSE) {
      $vars['tabs'] .= $tab . "\n";
    } else {
      if (!module_exists('wisski_textmod')) {
        $text = $tab;
        $text = str_replace('Edit', 'Edit Text', $text);
        $node = $vars['node'];
        $indiv = $node->title;
        $obj = wisski_store_getObj();
        $namespaces = $obj->wisski_ARCAdapter_getNamespaces();
        $q = "";
        
        foreach ($namespaces as $name => $val) {
          $q .= "PREFIX $name:\t<$val>\n";
        }  
        
        $pred = "ecrm:P129i_is_subject_of";
    
        $q .= "SELECT * WHERE { <" . wisski_store_getObj()->wisski_ARCAdapter_delNamespace($indiv) . "> <" 
          .  wisski_store_getObj()->wisski_ARCAdapter_delNamespace($pred) . "> ?x . }";
        $rows =  wisski_store_getObj()->wisski_ARCAdapter_getStore()->query($q, 'rows');

        // by Martin: if noo text  found, search via Document group path
        if (!$rows && module_exists('wisski_textproc')) {
          $text_uris = wisski_textproc_get_texts(wisski_store_getObj()->wisski_ARCAdapter_delNamespace($indiv));
          $rows = array(array('x' => $text_uris[0]));
        }
        // end by Martin

        $url = parse_url($rows[0]['x']);

        if($rows[0]['x']) {
          $vars['tabs'] .= '<li><a href="' . url() . drupal_lookup_path('source', ('content/' . wisski_store_getObj()->wisski_ARCAdapter_addNamespace($rows[0]['x'])))  . '/annotext?uri=' . urlencode(wisski_store_getObj()->wisski_ARCAdapter_delnamespace($indiv)) . '">Edit Text</a></li>';
        } else {
    
          $q = "";
          foreach ($namespaces as $name => $val) {
            $q .= "PREFIX $name:\t<$val>\n";
          }
      
          $q .= "SELECT * WHERE { <" . wisski_store_getObj()->wisski_ARCAdapter_delNamespace($indiv) . "> "
            . "rdf:type ?x . }";
        
          $rows =  wisski_store_getObj()->wisski_ARCAdapter_getStore()->query($q, 'rows');
          module_load_include('inc', 'wisski_pathbuilder');
          
          $groups = wisski_pathbuilder_getGroups();

          foreach($groups as $group) {
            $samepart = _wisski_pathbuilder_calculate_group_samepart($group);
            if($samepart["x" . (floor(count($samepart)/2))] == $rows[0]['x']) {
              $vars['tabs'] .= '<li><a href="' . url() . 'node/add/individual' . '/annotext/' . $group . '?uri=' . urlencode($indiv) .'">Create Text</a></li>';
              break;
            }
          }
        }
      }
    
      $vars['tabs'] .= str_replace('Edit', 'Edit Form', $tab) . "\n";
    }
  }
}

function blendedmalts_button($element) {
  if (isset($element['#attributes']['class'])) {
    $element['#attributes']['class'] = 'form-'. $element['#button_type'] .' '. $element['#attributes']['class'];
  } else {
    $element['#attributes']['class'] = 'form-'. $element['#button_type'];
  }
  
  $return_string = '<input ';
  if ($element['#button_type'] == 'image') {
    $return_string .= 'type="image" ';
  } else {
    $return_string .= 'type="submit" ';
  }
  $return_string .= (empty($element['#name']) ? '' : 'name="'. $element['#name'] .'" ') .'value="'. check_plain($element['#value']) .'" '. drupal_attributes($element['#attributes']) ." />\n";;
  return $return_string;
}

/*
 * Thanks to fabio at http://www.varesano.net/blog/fabio/displaying%20last%20updated%20or%20changed%20date%20drupal%20node
 */
function blendedmalts_node_submitted($node) {
  $time_unit = 86400; // number of seconds in 1 day => 24 hours * 60 minutes * 60 seconds
  $threshold = 1;

  if ($node->changed && (round(($node->changed - $node->created) / $time_unit) > $threshold)){ // difference between created and changed times > than threshold
    return t('Last updated on @changed. <br/>Originally submitted by !username on @created.', array(
             '@changed' => format_date($node->changed, 'medium'),
             '!username' => theme('username', $node),
             '@created' => format_date($node->created, 'small'),
    ));
  } else {
    return t('Submitted by !username on @datetime.',
      array(
        '!username' => theme('username', $node),
        '@datetime' => format_date($node->created),
    ));
  }
}

function blendedmalts_fieldset($element) {

  if (!empty($element['#collapsible'])) {
    drupal_add_js('misc/collapse.js');
      
  if (!isset($element['#attributes']['class'])) {
    $element['#attributes']['class'] = '';
  }
                    
  $element['#attributes']['class'] .= ' collapsible';
    if (!empty($element['#collapsed'])) {
      $element['#attributes']['class'] .= ' collapsed';
    }
  }
                                        
  return '<fieldset' . drupal_attributes($element['#attributes']) . '>' . ($element['#title'] ? '<legend>' . $element['#title'] . '</legend>' : '') . (isset($element['#description']) && $element['#description'] ? '<div class="description">' . $element['#description'] . '</div>' : '') . (!empty($element['#children']) ? $element['#children'] : '') . (isset($element['#value']) ? $element['#value'] : '') . "</fieldset>\n";
}

function blendedmalts_form_element($element, $value) {
// This is also used in the installer, pre-database setup.
  $t = get_t();
  
  $output = '<div class="form-item"';
  if (!empty($element['#id'])) {
    $output .= ' id="' . $element['#id'] . '-wrapper"';
  }
  $output .= ">\n";
  $required = !empty($element['#required']) ? '<span class="form-required" title="' . $t('This field is required.') . '">*</span>' : '';

  global $base_url;

//  $help = !empty($element['#attributes']['wisski_help']) ? '<span class="form-wisski-tooltip"><img src="' . $base_url . '/' . path_to_theme() . '/pics/help_small.png" alt="?" width="15px"/><em>' . $t($element['#attributes']['wisski_help']) . '</em></span>' : '';
  $help = !empty($element['#attributes']['wisski_help']) ? '<span class="form-wisski-tooltip">?<em>' . $t($element['#attributes']['wisski_help']) . '</em></span>' : '';
                
  if (!empty($element['#title'])) {
    $title = $element['#title'];
    if (!empty($element['#id'])) {
      $output .= ' <label for="' . $element['#id'] . '">' . $t('!title: !required !help', array('!title' => filter_xss_admin($title), '!required' => $required, '!help' => $help)) . "</label>\n";
    }
    else {
      $output .= ' <label>' . $t('!title: !required !help', array('!title' => filter_xss_admin($title), '!required' => $required, '!help' => $help)) . "</label>\n";
    }
  }
             
  $output .= " $value\n";
                  
  if (!empty($element['#description'])) {
    $output .= ' <div class="description">' . $element['#description'] . "</div>\n";
  }
                          
  $output .= "</div>\n";
                            
  return $output;
}

?>
