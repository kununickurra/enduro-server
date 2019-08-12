<?php

class SearchAllItineraries extends BaseApiAction
{
    protected function performApiCall(array $urlParams, array $queryParams, $body)
    {
        return $this->dataAccess->queryForList("SELECT id, `name` from itinerary order by name");
    }
}

class SearchItineraryById extends BaseApiAction
{
    protected function performApiCall(array $urlParams, array $queryParams, $body)
    {
        $itineraryId = $urlParams["id"];
        $itinerary = $this->dataAccess->queryForObject("SELECT id, `name` from itinerary WHERE id = $itineraryId");
        $itineraryPath =  $this->dataAccess->queryForList("SELECT * from anchor WHERE itinerary_id = $itineraryId ORDER BY sequence");
        $itinerary["path"] = $itineraryPath;
        return $itinerary;
    }
}
