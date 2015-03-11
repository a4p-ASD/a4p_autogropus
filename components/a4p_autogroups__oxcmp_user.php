<?php

/**
 *	@author:	a4p ASD / Andreas Dorner
 *	@company:	apps4print / page one GmbH, Nürnberg, Germany
 *
 *
 *	@version:	1.0.0
 *	@date:		11.03.2015
 *
 *
 *	a4p_autogroups__oxcmp_user.php
 *
 *	apps4print - a4p_autogroups - neue Benutzer zu festgelegten Gruppen hinzufügen
 *
 */

// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------

class a4p_autogroups__oxcmp_user extends a4p_autogroups__oxcmp_user_parent {

	// ------------------------------------------------------------------------------------------------
	// ------------------------------------------------------------------------------------------------


	protected $o_a4p_debug_log					= null;


	// ------------------------------------------------------------------------------------------------
	// ------------------------------------------------------------------------------------------------

	public function __construct() {


		// ------------------------------------------------------------------------------------------------
		// init a4p_debug_log
		//
		/*
		$o_oxModule							= oxNew( "oxModule" );
		$o_oxModule->load( "a4p_debug_log" );
		if ( $o_oxModule->isActive() ) {

			$this->o_a4p_debug_log			= oxNew( "a4p_debug_log" );
			$this->o_a4p_debug_log->a4p_debug_log_init( true, __CLASS__ . ".txt", null );
		}
		//*/
		// ------------------------------------------------------------------------------------------------


	}

	// ------------------------------------------------------------------------------------------------

	/**
	 * @version		OXID CE 4.9.2
	 */

	/**
	 * First test if all MUST FILL fields were filled, then performed
	 * additional checking oxcmp_user::CheckValues(). If no errors
	 * occured - trying to create new user (oxuser::CreateUser()),
	 * logging him to shop (oxuser::Login() if user has entered password).
	 * If oxuser::CreateUser() returns false - this means user is
	 * allready created - we only logging him to shop (oxcmp_user::Login()).
	 * If there is any error with missing data - function will return
	 * false and set error code (oxcmp_user::iError). If user was
	 * created successfully - will return "payment" to redirect to
	 * payment interface.
	 *
	 * Template variables:
	 * <b>usr_err</b>
	 *
	 * Session variables:
	 * <b>usr_err</b>, <b>usr</b>
	 *
	 * @return  mixed	redirection string or true if successful, false otherwise
	 */
	public function createUser() {


		$blActiveLogin = $this->getParent()->isEnabledPrivateSales();

		$oConfig = $this->getConfig();

		if ($blActiveLogin && !$oConfig->getRequestParameter('ord_agb') && $oConfig->getConfigParam('blConfirmAGB')) {
			oxRegistry::get("oxUtilsView")->addErrorToDisplay('READ_AND_CONFIRM_TERMS', false, true);

			return;
		}

		// collecting values to check
		$sUser = $oConfig->getRequestParameter('lgn_usr');

		// first pass
		$sPassword = $oConfig->getRequestParameter('lgn_pwd', true);

		// second pass
		$sPassword2 = $oConfig->getRequestParameter('lgn_pwd2', true);

		$aInvAdress = $oConfig->getRequestParameter('invadr', true);
		$aDelAdress = $this->_getDelAddressData();

		/** @var oxUser $oUser */
		$oUser = oxNew('oxuser');

		try {

			$oUser->checkValues($sUser, $sPassword, $sPassword2, $aInvAdress, $aDelAdress);

			$iActState = $blActiveLogin ? 0 : 1;

			// setting values
			$oUser->oxuser__oxusername = new oxField($sUser, oxField::T_RAW);
			$oUser->setPassword($sPassword);
			$oUser->oxuser__oxactive = new oxField($iActState, oxField::T_RAW);

			// used for checking if user email currently subscribed
			$iSubscriptionStatus = $oUser->getNewsSubscription()->getOptInStatus();

			$oUser->createUser();
			$oUser->load($oUser->getId());
			$oUser->changeUserData($oUser->oxuser__oxusername->value, $sPassword, $sPassword, $aInvAdress, $aDelAdress);

			if ($blActiveLogin) {
				// accepting terms..
				$oUser->acceptTerms();
			}

			$sUserId = oxRegistry::getSession()->getVariable("su");
			$sRecEmail = oxRegistry::getSession()->getVariable("re");
			if ($this->getConfig()->getConfigParam('blInvitationsEnabled') && $sUserId && $sRecEmail) {
				// setting registration credit points..
				$oUser->setCreditPointsForRegistrant($sUserId, $sRecEmail);
			}

			// assigning to newsletter
			$blOptin = oxRegistry::getConfig()->getRequestParameter('blnewssubscribed');
			if ($blOptin && $iSubscriptionStatus == 1) {
				// if user was assigned to newsletter
				// and is creating account with newsletter checked,
				// don't require confirm
				$oUser->getNewsSubscription()->setOptInStatus(1);
				$oUser->addToGroup('oxidnewsletter');
				$this->_blNewsSubscriptionStatus = 1;
			} else {
				$blOrderOptInEmailParam = $this->getConfig()->getConfigParam('blOrderOptInEmail');
				$this->_blNewsSubscriptionStatus = $oUser->setNewsSubscription($blOptin, $blOrderOptInEmailParam);
			}

			$oUser->addToGroup('oxidnotyetordered');


// ------------------------------------------------------------------------------------------------
// a4p ASD - erweiterte, automatische Gruppenzuordnung

$this->a4p_add_to_auto_group( $oUser );
// ------------------------------------------------------------------------------------------------


			$oUser->logout();

		} catch (oxUserException $oEx) {
			oxRegistry::get("oxUtilsView")->addErrorToDisplay($oEx, false, true);

			return false;
		} catch (oxInputException $oEx) {
			oxRegistry::get("oxUtilsView")->addErrorToDisplay($oEx, false, true);

			return false;
		} catch (oxConnectionException $oEx) {
			oxRegistry::get("oxUtilsView")->addErrorToDisplay($oEx, false, true);

			return false;
		}

		if (!$blActiveLogin) {

			oxRegistry::getSession()->setVariable('usr', $oUser->getId());
			$this->_afterLogin($oUser);


			// order remark
			//V #427: order remark for new users
			$sOrderRemark = oxRegistry::getConfig()->getRequestParameter('order_remark', true);
			if ($sOrderRemark) {
				oxRegistry::getSession()->setVariable('ordrem', $sOrderRemark);
			}
		}

		// send register eMail
		//TODO: move into user
		if ((int) oxRegistry::getConfig()->getRequestParameter('option') == 3) {
			$oxEMail = oxNew('oxemail');
			if ($blActiveLogin) {
				$oxEMail->sendRegisterConfirmEmail($oUser);
			} else {
				$oxEMail->sendRegisterEmail($oUser);
			}
		}

		// new registered
		$this->_blIsNewUser = true;

		$sAction = 'payment?new_user=1&success=1';
		if ($this->_blNewsSubscriptionStatus !== null && !$this->_blNewsSubscriptionStatus) {
			$sAction = 'payment?new_user=1&success=1&newslettererror=4';
		}

		return $sAction;


	}

	// ------------------------------------------------------------------------------------------------

	protected function a4p_add_to_auto_group( $oUser ) {


		// ------------------------------------------------------------------------------------------------
		// Benutzer bei Eingabe von Firma + UST-ID automatisch in Gruppe "Händler" ( "oxiddealer" ) aufnehmen

		$bSetting_addToDealer					= $this->getConfig()->getConfigParam( "a4p_autogroups_addToDealer" );

		if ( $bSetting_addToDealer ) {

			$bCheckDealerFields					= true;

			// ------------------------------------------------------------------------------------------------
			// relevante Felder prüfen
			if ( !$oUser->oxuser__oxcompany->value )
				$bCheckDealerFields				= false;

			if ( !$oUser->oxuser__oxustid->value )
				$bCheckDealerFields				= false;

			//checkVatId( $oUser, $aInvAddress )

			// ------------------------------------------------------------------------------------------------
			// UST-ID genauer prüfen
			if ( !$this->a4p_checkUstId( $oUser ) ) {
				$bCheckDealerFields				= false;
			}
			// ------------------------------------------------------------------------------------------------


			// ------------------------------------------------------------------------------------------------
			// bei erfolgreicher Prüfung in Gruppe aufnehmen
			#if ( $bCheckDealerFields && !$this->getUser()->inGroup( "oxiddealer" ) ) {
			if ( $bCheckDealerFields && !$oUser->inGroup( "oxiddealer" ) ) {

				$oUser->addToGroup( "oxiddealer" );
			}
		}
		// ------------------------------------------------------------------------------------------------


		// ------------------------------------------------------------------------------------------------
		// Module-Setting für neue Gruppenaufnahme holen

		$sSetting_addToGroup					= $this->getConfig()->getConfigParam( "a4p_autogroups_addToGroup" );
		$aSetting_addToGroup					= explode( ",", $sSetting_addToGroup );

		foreach( $aSetting_addToGroup as $key => $curGroup ) {

			$curGroup							= trim( $curGroup );

			#$this->getUser()->addToGroup( $curGroup );
			$oUser->addToGroup( $curGroup );
		}
		// ------------------------------------------------------------------------------------------------


		// ------------------------------------------------------------------------------------------------
		// Benutzer in Gruppe mit gewähltem Land aufnehmen

		$bSetting_addToCountryGroup				= $this->getConfig()->getConfigParam( "a4p_autogroups_addToCountryGroup" );

		if ( $bSetting_addToCountryGroup ) {

			$sCurUserCountryID					= $oUser->oxuser__oxcountryid->value;

			// ------------------------------------------------------------------------------------------------
			// Land für oxcountryid und Gruppe mit selben Title ermitteln
			$sSQL								= "SELECT oxgroups.OXID FROM oxgroups";
			$sSQL								.= " INNER JOIN oxcountry ON oxgroups.OXTITLE = oxcountry.OXTITLE";
			$sSQL								.= " WHERE oxcountry.OXID = '" . $sCurUserCountryID . "'";

			$rows								= oxDb::getDb( 2 )->Execute( $sSQL );
			$oxgroups__oxid						= false;
			if( $rows != false && $rows->recordCount() > 0 && !$rows->EOF ) {
				$oxgroups__oxid					= $rows->fields[ "OXID" ];
			}
			if ( $oxgroups__oxid ) {

				#$this->getUser()->addToGroup( $oxgroups__oxid );
				$oUser->addToGroup( $oxgroups__oxid );
			}

		}
		// ------------------------------------------------------------------------------------------------

	}

	// ------------------------------------------------------------------------------------------------

	/**
	 * @desc	UST-ID genauer prüfen
	 */

	protected function a4p_checkUstId( $oUser ) {


		// ------------------------------------------------------------------------------------------------
		// Land laden
		$sCountryId								= $oUser->oxuser__oxcountryid->value;
		if ( !$sCountryId ) {

			return false;

		} else {

			$oCountry							= oxNew( 'oxcountry' );
			$oCountry->load( $sCountryId );

			if ( !$oCountry->isForeignCountry() )
				return false;

			/*
			 *	DEBUG:
				error_reporting( E_ALL );
				if ( $oCountry->isForeignCountry() )
					trigger_error( "isForeignCountry OK" );
				else
					trigger_error( "isForeignCountry NO" );
				if ( $oCountry->isInEU() )
					trigger_error( "isInEU OK" );
				else
					trigger_error( "isInEU NO" );
			*/

			if ( !$oCountry->isInEU() )
				return false;

			if ( strncmp( $oUser->oxuser__oxustid->value, $oCountry->oxcountry__oxisoalpha2->value, 2 ) === 0 )
				return true;
			else
				return false;
		}

	}

	// ------------------------------------------------------------------------------------------------

}

// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------
