<?php

class SearchAllTrips extends BaseApiAction
{
    protected function performApiCall(array $urlParams, array $queryParams, $body)
    {
        $dataAccess = DataAccessManagerFactory::getInstance();
        return $this->dataAccess->queryForList("SELECT * from trip order by name");
    }
}

class SearchTripLogs extends BaseApiAction
{
    protected function performApiCall(array $urlParams, array $queryParams, $body)
    {
        $tripId = $queryParams["trip_id"];
        return $this->dataAccess->queryForList("SELECT * from trip_log WHERE trip_id = $tripId ORDER BY occurred");
    }
}
