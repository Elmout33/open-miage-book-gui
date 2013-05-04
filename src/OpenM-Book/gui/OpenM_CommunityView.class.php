<?php

Import::php("OpenM-Book.gui.OpenM_BookView");
Import::php("util.JSON.OpenM_MapConvertor");

/**
 * Description of OpenM_CommunityView
 *
 * @author Nico
 */
class OpenM_CommunityView extends OpenM_BookView {

    public function _default() {
        $this->view();
    }

    public function view() {

        $this->smarty->assign(self::MENU_COMMUNITY, TRUE);
        $this->initPage();

    
        
        
        $me = OpenM_SessionController::get(self::MY_DATA);
        $this->smarty->assign("nom", $me->get("ULN"));
        $this->smarty->assign("prenom", $me->get("UFN"));                
        
        $this->smarty->assign("UserJSON", OpenM_MapConvertor::mapToJSON($me));
        
        
        

        OpenM_Log::debug("Community View Open", __CLASS__, __METHOD__, __LINE__);
        $this->showAlert();
        $this->smarty->display('community.tpl');
    }

    private function initPage() {
        $connected = $this->isConnected();

        $this->addLinks();
        $this->addNavBarItems();
        $this->addClientsJS();
    }

}

?>
