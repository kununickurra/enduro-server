<?php

class DistributionChannelAction extends BaseApiAction
{
    protected function performApiCall(array $urlParams, array $queryParams, $body)
    {
        return $this->dataAccess->queryForList(
            "SELECT trn_distribution_channel_id as id, trn_distribution_group_id as distribution_group_id, name
			 FROM trn_distribution_channel WHERE end_date = '9999-12-31' and country_id = $this->countryId
			 AND online_form=1 ORDER by TRIM(name)");

    }
}

class PaymentOptionsApiAction extends BaseApiAction
{
    protected function performApiCall(array $urlParams, array $queryParams, $body)
    {
        return $this->dataAccess->queryForList(
            "SELECT dim_payment_option.dim_payment_option_id AS id, dim_payment_option.name AS name FROM dim_payment_option");
    }
}


class ClientOriginApiAction extends BaseApiAction
{
    protected function performApiCall(array $urlParams, array $queryParams, $body)
    {
        return $this->dataAccess->queryForList(
            "SELECT trn_origin_id as id, name from trn_origin WHERE end_date = '9999-12-31' and country_id = $this->countryId ORDER BY name DESC");
    }
}


class PathologiesApiAction extends BaseApiAction
{
    protected function performApiCall(array $urlParams, array $queryParams, $body)
    {
        return $this->dataAccess->queryForList(
            "SELECT dim_pathology_id AS id, name FROM dim_pathology WHERE end_date = '9999-12-31' ORDER BY sort_order ASC");
    }
}


class LanguagesApiAction extends BaseApiAction
{
    protected function performApiCall(array $urlParams, array $queryParams, $body)
    {
        return $this->dataAccess->queryForList(
            "SELECT dim_language_id as id, name, culture FROM  dim_language WHERE end_date = '9999-12-31'");
    }
}

class ProductGroupsApiAction extends BaseApiAction
{
    protected function performApiCall(array $urlParams, array $queryParams, $body)
    {
        return $this->dataAccess->queryForList(
            "SELECT  trn_product_group_id as id, name FROM trn_product_group WHERE end_date = '9999-12-31' and country_id = $this->countryId ORDER BY name asc");
    }
}

class ProductsApiAction extends BaseApiAction
{
    protected function performApiCall(array $urlParams, array $queryParams, $body)
    {
        return $this->dataAccess->queryForList(
            "SELECT trn_product_id as id, trn_product_group_id as product_group_id, name, abbreviation, base_price
			 FROM trn_product WHERE end_date = '9999-12-31' and country_id = $this->countryId ORDER BY TRIM(name)");
    }
}


class DistributionGroupsAction extends BaseApiAction
{
    protected function performApiCall(array $urlParams, array $queryParams, $body)
    {
        return $this->dataAccess->queryForList(
            "SELECT trn_distribution_group_id as id, name 
			 FROM trn_distribution_group WHERE end_date = '9999-12-31' and country_id = $this->countryId  
			 ORDER by TRIM(name)");
    }
}

?>