<?php

class PendingOrderCreationAction extends BaseApiAction
{
    protected function performApiCall(array $urlParams, array $queryParams, $body)
    {
        $dataAccess = DataAccessManagerFactory::getInstance();

        $body = (array) $body;

        $salesPersonId = $this->validateField("sales_person_id", $body, "INT", true);
        $this->validateSalesperson($salesPersonId);

        $distributionChannelId = $this->validateField("distribution_channel_id", $body, "INT", true);
        $orderDate = $this->validateField("order_date", $body, "DATE", true);
        $location = $this->validateField("location", $body, "STRING", true);
        $clientCRMId = $this->validateField("client_crm_id", $body, "STRING");
        $clientCRMLeadSource = $this->validateField("client_crm_lead_source", $body, "STRING");
        $clientLanguageId = $this->validateField("client_language_id", $body, "INT", true);
        $clientFirstName = $this->validateField("client_first_name", $body, "STRING");
        $clientLastName = $this->validateField("client_last_name", $body, "STRING");
        $clientBirthDate = $this->validateField("client_birth_date", $body, "DATE");
        $clientIdentityNumber = $this->validateField("client_identity_number", $body, "STRING");
        $companyName = $this->validateField("company_name", $body, "STRING");
        $companyVATNumber = $this->validateField("company_vat_number", $body, "STRING");
        $clientPhoneNumber = $this->validateField("client_phone_number", $body, "STRING");
        $clientMobileNumber = $this->validateField("client_mobile_number", $body, "STRING");
        $clientEmail = $this->validateField("client_email", $body, "EMAIL");
        $clientStreet = $this->validateField("client_street", $body, "STRING", true);
        $clientPostcode = $this->validateField("client_postcode", $body, "STRING");
        $clientCity = $this->validateField("client_city", $body, "STRING", true);
        $clientCountry = $this->validateField("client_country", $body, "STRING", true);
        $useDeliveryAddress = $this->validateField("use_delivery_address", $body, "BOOLEAN", true);
        $deliveryAddressRequired = ($useDeliveryAddress == 1) ? true : false;
        $deliveryStreet = $this->validateField("delivery_street", $body, "STRING", $deliveryAddressRequired);
        $deliveryPostcode = $this->validateField("delivery_postcode", $body, "STRING");
        $deliveryCity = $this->validateField("delivery_city", $body, "STRING", $deliveryAddressRequired);
        $deliveryCountry = $this->validateField("delivery_country", $body, "STRING", $deliveryAddressRequired);
        $pathologies = $this->validateField("pathologies", $body, "ARRAY");
        foreach ($pathologies as $key => $pathology) {
            $pathologies[$key] = $this->validateField($key, $body["pathologies"], "INT", true);
        }
        $pathologyOthers = $this->validateField("pathology_others", $body, "STRING");
        $acceptStudy = $this->validateField("accept_study", $body, "BOOLEAN", true, 0);
        $grandTotal = $this->validateField("grand_total", $body, "FLOAT", true);
        $remark = $this->validateField("remark", $body, "STRING");
        $paymentOptionId = $this->validateField("payment_option_id", $body, "INT", true);
        $orderItems = (array)$this->validateField("items", $body, "ARRAY", true);

        // Get all orders items
        foreach ($orderItems as $key => $orderItem) {
            $orderItems[$key]->product_id = $this->validateField("product_id", (array)$orderItems[$key], "INT", true);
            $orderItems[$key]->line_number = $this->validateField("line_number", (array)$orderItems[$key], "INT", true);
            $orderItems[$key]->quantity = $this->validateField("quantity", (array)$orderItems[$key], "INT", true);
            $orderItems[$key]->product_name = $this->validateField("product_name", (array)$orderItems[$key], "STRING", true);
            $orderItems[$key]->base_price = $this->validateField("base_price", (array)$orderItems[$key], "FLOAT", true);
            $orderItems[$key]->discount = $this->validateField("discount", (array)$orderItems[$key], "FLOAT", true);
            $orderItems[$key]->final_price = $this->validateField("final_price", (array)$orderItems[$key], "FLOAT", true);

        }

        $clientCountryName = $dataAccess->queryForField("SELECT name FROM dim_country where end_date = '9999-12-31' and country_code = '" . mysql_real_escape_string($clientCountry) . "'");
        $deliveryCountryName = $dataAccess->queryForField("SELECT name FROM dim_country where end_date = '9999-12-31' and country_code = '" . mysql_real_escape_string($deliveryCountry) . "'");

        $this->validateClientNameOrCompanyNameNotEmpty($clientLastName, $companyName);

        try {

            $dataAccess->startTransaction();
            $dataAccess->executeStatement("LOCK TABLES dim_country WRITE, trn_pending_order WRITE, trn_pending_order_item WRITE");
            $pendingOrderId = $dataAccess->queryForField("SELECT IFNULL(MAX(trn_pending_order_id) + 1, 1) as newId FROM trn_pending_order");

            $sql = "
			INSERT INTO trn_pending_order (
				`trn_pending_order_id`,`trn_distribution_channel_id`,`order_date`,`location`
				,`trn_sales_person_id`,`trn_sales_person_id_2`,`first_name`,`last_name`,`date_of_birth`, `company_name`
				,`vat_num`,`telephone`,`mobile`,`email`, `street`,`postcode`, `city`, `country`
				,`delivery_check`,`delivery_street`,`delivery_postcode`,`delivery_city`, `delivery_country`
				,`language_id`,`accept_study`,`pathology`,`pathology_others`,`grand_total`,`remark`, payment_options
				,`country_id`,`created_by`,`identity_number`, `client_crm_id`, `client_crm_lead_source`, `start_date`,`end_date`)
			VALUES (" . mysql_real_escape_string($pendingOrderId) .
                "," . mysql_real_escape_string($distributionChannelId) .
                ",'" . mysql_real_escape_string($orderDate) .
                "','" . mysql_real_escape_string($location) .
                "'," . mysql_real_escape_string($salesPersonId) .
                "," . mysql_real_escape_string(0) .
                ",'" . mysql_real_escape_string($clientFirstName) .
                "','" . mysql_real_escape_string($clientLastName) .
                "','" . mysql_real_escape_string($clientBirthDate) .
                "','" . mysql_real_escape_string($companyName) .
                "','" . mysql_real_escape_string($companyVATNumber) .
                "','" . mysql_real_escape_string($clientPhoneNumber) .
                "','" . mysql_real_escape_string($clientMobileNumber) .
                "','" . mysql_real_escape_string($clientEmail) .
                "', '" . mysql_real_escape_string($clientStreet) .
                "','" . mysql_real_escape_string($clientPostcode) .
                "','" . mysql_real_escape_string($clientCity) .
                "','$clientCountryName'" .
                ",'" . mysql_real_escape_string($useDeliveryAddress) .
                "','" . mysql_real_escape_string($deliveryStreet) .
                "','" . mysql_real_escape_string($deliveryPostcode) .
                "','" . mysql_real_escape_string($deliveryCity) .
                "','$deliveryCountryName'" .
                "," . mysql_real_escape_string($clientLanguageId) .
                "," . mysql_real_escape_string($acceptStudy) .
                ",'" . mysql_real_escape_string(implode(',', $pathologies)) .
                "','" . mysql_real_escape_string($pathologyOthers) .
                "','" . mysql_real_escape_string($grandTotal) .
                "','" . mysql_real_escape_string($remark) .
                "','" . mysql_real_escape_string($paymentOptionId) .
                "'," . mysql_real_escape_string($urlParams["country_id"]) .
                ",0" .
                ",'" . mysql_real_escape_string($clientIdentityNumber) .
                "','" . mysql_real_escape_string($clientCRMId) .
                "','" . mysql_real_escape_string($clientCRMLeadSource) .
                "',CURRENT_TIMESTAMP,'9999-12-31')";

            $dataAccess->executeStatement($sql);

            $pendingOrderItemId = $dataAccess->queryForField("SELECT IFNULL(MAX(trn_pending_order_item_id) + 1, 1) as newId FROM trn_pending_order_item");

            foreach ($orderItems as $key => $orderItem) {
                $sql = "
						INSERT INTO `trn_pending_order_item` (
							`trn_pending_order_item_id`,`trn_pending_order_id`,`trn_product_id`,`row`,`quantity`,`product_name`
							,`base_price`,`discount`,`final_price`,`start_date`,`end_date`)
						VALUES (
						" . $pendingOrderItemId++ .
                    "," . $pendingOrderId .
                    "," . mysql_real_escape_string($orderItem->product_id) .
                    "," . mysql_real_escape_string($orderItem->line_number) .
                    "," . mysql_real_escape_string($orderItem->quantity) .
                    ",'" . mysql_real_escape_string($orderItem->product_name) .
                    "'," . mysql_real_escape_string($orderItem->base_price) .
                    "," . mysql_real_escape_string($orderItem->discount) .
                    "," . mysql_real_escape_string($orderItem->final_price) .
                    ",CURRENT_TIMESTAMP,'9999-12-31')";
                $dataAccess->executeStatement($sql);
            }

            $dataAccess->executeStatement("UNLOCK TABLES");
            $dataAccess->commitTransaction();
            return;
        } catch (Exception $e) {
            echo $e;
            if ($dataAccess->isInTransaction()) {
                $dataAccess->rollbackTransaction();
            }
        }
    }

    private function validateClientNameOrCompanyNameNotEmpty($clientLastName, $companyName) {
        if($this->isFieldEmpty($clientLastName) && $this->isFieldEmpty($companyName)) {
            $apiError = new ApiError("400", "Invalid request...");
            $apiError->addDetailError("mandarory_field", "Please provide at least the company_name or the client_last_name field");
            throw new BadRequestException($apiError);
        }
    }

    private function validateSalesperson($salesPersonId) {
        $dataAccess = DataAccessManagerFactory::getInstance();
        $salesPerson = $dataAccess->queryForField(
            "SELECT trn_sales_person_id AS sales_person_id FROM trn_sales_person 
             WHERE trn_sales_person_id='$salesPersonId' 
             AND country_id = $this->countryId 
             AND end_date = '9999-12-31'");
        if ($salesPerson == null) {
            throw new UnauthorizedAccessException("No salesperson with id [$salesPersonId] found for country [$this->countryId]");
        }

    }
}

?>

