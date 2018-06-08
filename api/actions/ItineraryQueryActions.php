<?php

class SearchAllItineraries extends BaseApiAction
{
    protected function performApiCall(array $urlParams, array $queryParams, $body)
    {
        return $this->dataAccess->queryForList("SELECT id, `name` from itinerary order by name");
    }
}

class SearchItineraryPath extends BaseApiAction
{
    protected function performApiCall(array $urlParams, array $queryParams, $body)
    {
        $itineraryId = $queryParams["itinerary_id"];
        return $this->dataAccess->queryForList("SELECT * from anchor WHERE itinerary_id = $itineraryId ORDER BY sequence");
    }
}
