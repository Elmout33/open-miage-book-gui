<?php

Import::php("OpenM-Book.view.OpenM_BookView");
Import::php("OpenM-Controller.view.OpenM_URLViewController");
Import::php("OpenM-Services.client.OpenM_ServiceSSOClientImpl");
Import::php("util.session.OpenM_SessionController");

/**
 * 
 * @author Gaël Saunier
 * 
 */
class OpenM_RegistrationView extends OpenM_BookView {

    const LAST_NAME = "last_name";
    const FIRST_NAME = "first_name";
    const YEAR = "year";
    const MONTH = "month";
    const MONTHNUM = "monthNum";
    const DAY = "day";
    const EMAIL = "email";
    const CGU = "cgu";
    const BIRTHDAY = "birthday";
    const ERROR = "error";
    const ERROR_MESSAGE = "error_message";
    const REGISTER_FORM = "register";
    const CONDITION_FORM = "condition";
    const LOGIN_FORM = "login";
    const SMARTY_REGISTER_KEYS_ARRAY = "register_form";

    public function _default() {
        $this->login();
    }

    /*
     * Méthode permetant d'envoyer l'utilisateur vers la page d'authetification (OpenM_ID) 
     * puis de rediriger vers l'index ou la méthode register si l'utilisateur n'existe pas dans OpenM_Book
     */

    public function login() {
        $this->sso_book->login(array(OpenM_ID::EMAIL_PARAMETER), TRUE);
        try {
            $me = $this->bookClient->getUserProperties();
            //todo saved in session $me and redirect
            OpenM_Log::debug("User conected, and registred", __CLASS__, __METHOD__, __LINE__);

            OpenM_SessionController::set(self::MY_DATA, $me);
            OpenM_Header::redirect(OpenM_URLViewController::from()->getURL());
        } catch (Exception $e) {
            OpenM_Header::redirect(OpenM_URLViewController::from(self::getClass(), self::REGISTER_FORM)->getURL());
        }
    }

    public function register() {

        $this->isConnected();

        if (OpenM_SessionController::contains(self::MY_DATA)) {
            OpenM_Log::debug("Useralready registred, redirect to Profile", __CLASS__, __METHOD__, __LINE__);
             $this->setAlert( "Vous êtes déjà enregistrer.");
            OpenM_Header::redirect(OpenM_URLViewController::from(OpenM_ProfileView::getClass())->getURL());
        }

        $error = FALSE;
        $param = HashtableString::from($_POST);
        if ($param->containsKey("submit")) {
            if ($param->get(self::LAST_NAME) == "") {
                $error = TRUE;
                $error_message = "veuillez saisir votre nom";
            }
            if ($param->get(self::FIRST_NAME) == "") {
                $error = TRUE;
                $error_message = "veuillez saisir votre prénom";
            }

            $day = $param->get(self::DAY)->toInt();
            $month = $param->get(self::MONTHNUM)->toInt();
            $year = $param->get(self::YEAR)->toInt();
            if ($day === 0 || $year === 0 || $month === 0) {
                $error = TRUE;
                $error_message = "veuillez saisir votre date de naissance correctement";
            } else {
                $time = mktime(0, 0, 0, $day, $month, $year);
                if ($time === FALSE) {
                    $error = TRUE;
                    $error_message = "veuillez saisir votre date de naissance correctement";
                }
            }
            $mail = $param->get(self::EMAIL);
            if ($mail == "") {
                $mail = $this->sso_book->getProperties()->get(OpenM_ID::EMAIL_PARAMETER);
                if ($mail == null) {
                    $error = TRUE;
                    $error_message = "veuillez saisir votre eMail";
                }
            }

            if ($param->get(self::CGU) == "") {
                $error = TRUE;
                $error_message = "veuillez accepter la charte pour continuer";
            }

            if (!$error) {
                $clientBook = new OpenM_ServiceSSOClientImpl($this->sso_book, "OpenM_Book");
                try {
                    $retour = $clientBook->registerMe($param->get(self::FIRST_NAME), $param->get(self::LAST_NAME), $time);
                    /**
                     * @todo faire code, récupération ID de la propriété email
                     */
                    $retour = $clientBook->addPropertyValue(2, ($param->get(self::EMAIL)));

                    $me = $clientBook->getUserProperties();
                    OpenM_SessionController::set(self::MY_DATA, $me);

                    //le message du succes d'enregistrement
                    OpenM_SessionController::set(self::ALERT, "<h4>Succès de l'enregistrement</h4>Bienvenue sur votre profil.<br>Nous vous conseillons de mettre à jours vos informations");
                    //tous c'est bien passé, on redirige vers le profil
                    OpenM_Header::redirect(OpenM_URLViewController::from(OpenM_ProfileView::getClass())->getURL());
                } catch (Exception $e) {
                    $error = TRUE;
                    $error_message = $e->getMessage();
                }
            }
        }
        else
            $mail = $this->sso_book->getProperties()->get(OpenM_ID::EMAIL_PARAMETER);


        $this->smarty->assign(self::SMARTY_REGISTER_KEYS_ARRAY, array(
            self::LAST_NAME => array(
                "key" => self::LAST_NAME,
                "label" => self::LAST_NAME,
                "value" => $param->get(self::LAST_NAME)
            ),
            self::FIRST_NAME => array(
                "key" => self::FIRST_NAME,
                "label" => self::FIRST_NAME,
                "value" => $param->get(self::FIRST_NAME)
            ),
            self::BIRTHDAY => "Birthday",
            self::DAY => array(
                "key" => self::DAY,
                "label" => self::DAY,
                "value" => $param->get(self::DAY)
            ),
            self::YEAR => array(
                "key" => self::YEAR,
                "label" => self::YEAR,
                "value" => $param->get(self::YEAR)
            ),
            self::MONTH => array(
                "key" => self::MONTH,
                "label" => self::MONTH,
                "idHiden" => self::MONTHNUM
            ), self::EMAIL => array(
                "key" => self::EMAIL,
                "label" => self::EMAIL,
                "value" => $mail
            ),
            self::CGU => self::CGU,
        ));

        $this->smarty->assign(self::SMARTY_REGISTER_KEYS_ARRAY . "_condition", OpenM_URLViewController::from(self::getClass(), self::CONDITION_FORM)->getURL());

        if ($error) {
            $this->smarty->assign(self::ERROR, $error);
            $this->smarty->assign(self::ERROR_MESSAGE, $error_message);
        }

        $this->addLinks();
        $this->addNavBarItems();
        $this->smarty->display('inscription.tpl');
    }

    public function condition() {
        $this->addLinks();
        $this->addNavBarItems();
        $this->smarty->display('condition.tpl');
    }

}

?>