<?php

class ProductsByClientSyncApiAction extends BaseApiAction
{
    protected function performApiCall(array $urlParams, array $queryParams, $body)
    {
        $timestamp_since = $this->validateQueryParameter("order_modified_since", $queryParams, "DATETIME", true);
        $currentDate = new DateTime();
        $timestamp_until = $this->validateQueryParameter("order_modified_until", $queryParams, "DATETIME", false, $currentDate->format('Y-m-d H:i:s'));

        $since = DateTime::createFromFormat('Y-m-d H:i:s', $timestamp_since);
        $until = DateTime::createFromFormat('Y-m-d H:i:s', $timestamp_until);


        // Verify that the end_date is greater that the start date.
        $timeDiff = $since->diff($until);
        if($timeDiff->invert == 1) {
            $apiError = new ApiError("401", DataValidator::$errors["401"]);
            $apiError->addDetailError("field_value_error", "The order_modified_until [$timestamp_until] is before order_modified_since [".$timestamp_since."]", "order_modified_until");
            throw new BadRequestException($apiError);
        }

        // Verify that the range does not exceeds 7 days.
        if ($timeDiff->days > 7) {
            $apiError = new ApiError("401", DataValidator::$errors["401"]);
            $apiError->addDetailError("field_value_error", "The maximum range allowed for sync is 7 days, provided [$timeDiff->days]", "order_modified_until");
            throw new BadRequestException($apiError);
        }

        $query = "SELECT
                  trn_order.trn_client_id,
                  trn_client.trn_client_id as id,
                  trn_client.trn_client_ref as ref_number,
                  trn_client.crm_id,
                  trn_client.last_name,
                  trn_client.first_name,
                  group_concat(trn_order.trn_order_id) as order_items
            FROM trn_order
              JOIN trn_client ON (trn_client.trn_client_id = trn_order.trn_client_id AND trn_client.end_date = '9999-12-31')  
            WHERE trn_order.country_id = $this->countryId 
            AND trn_order.start_date > '$timestamp_since'";

            if ($timestamp_until != NULL) {
                $query = $query." AND trn_order.start_date < '$timestamp_until'";
            }

        $query = $query." AND trn_order.end_date = '9999-12-31' GROUP BY trn_order.trn_client_id order by trn_order.start_date";

        $clientsAndOrders = $this->dataAccess->queryForList($query);

        foreach($clientsAndOrders as $key => $clientAndOrders) {
             $orderItems = $this->dataAccess->queryForList(
                 "select pro.trn_product_id as product_id, 
                         pro.`name` as product_name, 
                         pro.`abbreviation`, 
                         grp.`trn_product_group_id` as product_group_id, 
                         grp.`name` as product_group,
                         o.trn_order_id as order_id,
                         o.order_ref_nbr as order_ref_number,
                   CASE WHEN o.status = 1
                     THEN 'ordered'
                     ELSE 'cancelled'
                   END AS status
                from trn_order as o
                  inner join trn_order_item as i
                    on i.trn_order_id = o.trn_order_id and o.end_date='9999-12-31'
                  inner join trn_product pro
                    on pro.trn_product_id = i.trn_product_id and pro.end_date ='9999-12-31'
                  inner join trn_product_group as grp
                    on grp.trn_product_group_id = pro.trn_product_group_id and grp.end_date ='9999-12-31'
                where o.trn_order_id  in (".$clientAndOrders["order_items"].")
                 and i.end_date='9999-12-31'");
            // Replace the order items Ids by the entity.
            $clientsAndOrders[$key]["order_items"] = $orderItems;
        }
        return $clientsAndOrders;
    }
}

?>