<?php

/**
 * Render Json
 *
 * @author Andrea Schmuttermair <andrea.schmuttermair@ninteno.de>
 */
class Tx_ImportExcel_ViewHelpers_RenderJsonViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {
    
  /**
   * Return yes or no for boolean Value
   *
   * @param string $json
   * @return string
   */
  public function render($json) {
    
        return html_entity_decode($json);
  }
  

  
}

?>