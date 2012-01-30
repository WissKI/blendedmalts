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
    blendedmalts_removetab('/edit', $vars);
}

function blendedmalts_viewadjust(&$vars, $mode = "node") {
  // Get the node object
  $node = &$vars['node'];

  include_once('sites/all/modules/wisski_pathbuilder/wisski_pathbuilder.inc');
  
  $groupid = wisski_pathbuilder_getGroupIDForIndividual(wisski_store_getObj()->wisski_ARCAdapter_delNamespace($node->title));

  if(!$groupid || !isset($groupid))
    return;
  
  if($mode == "page") {
    $vars['maltedtitle'] = wisski_pathbuilder_generateGroupName($node->title, $groupid);
  } else if($mode == "node") {
    $block2 = module_invoke('wisski_pathbuilder', 'block', 'view', 0);
    $block1 = module_invoke('wisski_pathbuilder', 'block', 'view', 1);
    $vars['maltedcontent'] = '<div id="wki-content-right">' . $block2['content'] . '</div>' . '<div id="wki-content-left">' . $block1['content'] .  '</div>';
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
        include_once('sites/all/modules/wisski_pathbuilder/wisski_pathbuilder.inc');

        $groups = wisski_pathbuilder_getGroups();

        foreach($groups as $group) {
          $samepart = _wisski_pathbuilder_calculate_group_samepart($group);
          if($samepart["x" . (floor(count($samepart)/2))] == $rows[0]['x']) {
            $vars['tabs'] .= '<li><a href="' . url() . 'node/add/individual' . '/annotext/' . $group . '?uri=' . urlencode($indiv) .'">Create Text</a></li>';
            break;
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

?>
