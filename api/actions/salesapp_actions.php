<?php

class InitSalesAppAction extends BaseApiAction
{
    protected function performApiCall(array $urlParams, array $queryParams, $body)
    {
        $salesPersonEmail = $this->validateField("email", $queryParams, "EMAIL", true);

        $salesPersons = $this->dataAccess->queryForList(
            "SELECT trn_sales_person_id AS sales_person_id FROM trn_sales_person 
             WHERE email='$salesPersonEmail' 
             AND country_id = $this->countryId 
             AND end_date = '9999-12-31'");

        if (sizeof($salesPersons) == 0) {
            throw new UnauthorizedAccessException("No salesperson found for the provided country [$this->countryId] and email [$salesPersonEmail]");
        }

        if (sizeof($salesPersons) > 1) {
            throw new UnauthorizedAccessException("More than one salesperson found for the provided country [$this->countryId] and email [$salesPersonEmail]");
        }

        return $salesPersons[0];
    }
}

?>