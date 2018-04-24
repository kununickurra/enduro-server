<?php

class ClientAction extends BaseApiAction {

    private static $BASE_CLIENT_QUERY = "SELECT 
                  trn_client_id as id,
                  trn_client_ref as ref_number,
                  crm_id,
                  crm_lead_source,
                  trn_origin_id as client_origin_id,
                  dim_language_id as language_id,
                  first_name,
                  last_name,
                  date_of_birth as birth_date,
                  profession,
                  personal_vat_nbr as personal_vat_number,
                  personal_identity_card as identity_number,
                  telephone as phone_number,
                  mobile as mobile_number,
                  mobile_second,
                  email,
                  payment_bank_account_number,
                  payment_bic_swift,
                  (select country_code from dim_country where `name` = trn_client.country and end_date = '9999-12-31') as country,
                  company_name,
                  company_vat_nbr as company_vat_number,
                  company_street,
                  company_postcode,
                  company_city,
                  (select country_code from dim_country where `name` = trn_client.company_country and end_date = '9999-12-31') as company_country,
                  delivery_last_name,
                  delivery_first_name,
                  delivery_street,
                  delivery_postcode,
                  delivery_city,
                  (select country_code from dim_country where `name` = trn_client.delivery_country and end_date = '9999-12-31') as delivery_country,
                  delivery_addr_longitude as delivery_address_longitude,
                  delivery_addr_latitude as delivery_address_latitude,
                  (SELECT group_concat(dim_pathology_id) from trn_client_pathology where trn_client_id = trn_client.trn_client_id and end_date = '9999-12-31') as pathologies,  
                  pathology_others,
                  additional_info,
                  do_not_contact_client,
                  reason_do_not_contact,
                  start_date as modified_at
                 from trn_client WHERE end_date = '9999-12-31'";

    protected function performApiCall(array $urlParams, array $queryParams, $body)
    {
        $query = self::$BASE_CLIENT_QUERY;

        $uniqueIdentification = isset($urlParams["unique_identifier"]) ? $urlParams["unique_identifier"] : NULL;
        if($uniqueIdentification != NULL) {
            // Query By Id or by reference number
            if(is_numeric($uniqueIdentification)) {
                $query = $query." AND trn_client_id = $uniqueIdentification";
            } else {
                $query = $query." AND trn_client_ref = '$uniqueIdentification'";
            }
            $client = $this->dataAccess->queryForObject($query);
            if($client === NULL) {
                throw new EntityNotFoundException("client", $uniqueIdentification);
            }
            return $client;
        }

        // Check if a list query is requested.

        $crm_id = isset($_GET["crm_id"]) ? $_GET["crm_id"] : NULL;

        $timestamp_since = $this->validateField("modified_since", $queryParams, "DATETIME", false);
        if($timestamp_since != NULL) {
            $currentDate = new DateTime();
            $timestamp_until = $this->validateField("modified_until", $queryParams, "DATETIME", false, $currentDate->format('Y-m-d H:i:s'));
        }

        if(empty($crm_id) && empty($timestamp_since)) {
            $apiError = new ApiError("402", "Please provide at least the [crm_id] or the [modified_since] parameter");
            throw new BadRequestException($apiError);
        }

        if(!empty($crm_id)) {
            $query = $query." AND crm_id = '$crm_id'";
        }

        if(!empty($timestamp_since)) {
            $query = $query." AND start_date > '$timestamp_since'";
        }

        if(!empty($timestamp_since)) {
            $query = $query." AND start_date < '$timestamp_until'";
        }

        $clients = $this->dataAccess->queryForList($query);

        $convertedClients = array();
        foreach($clients as $client) {
            $convertedClients[] = $this->convertToRepresentation($client);
        }
        return $convertedClients;


    }

    private function convertToRepresentation($data) {
        if(empty($data)) {
            return $data;
        }
        if(empty($data["pathologies"])) {
            $data["pathologies"] = array();
        } else {
            $data["pathologies"] = explode(",",$data["pathologies"]);
        }
        return $data;
    }

}

?>