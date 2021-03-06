<?php
class SaveItineraryActions extends BaseApiAction
{
    protected function performApiCall(array $urlParams, array $queryParams, $body)
    {
        $itinerary = (array) $body;
        $itineraryPath = $itinerary["path"];

        $this->dataAccess->startTransaction();

        // Add the Itinerary
        $sql = "INSERT INTO itinerary (id, `name`) VALUES (0, '". $itinerary["name"] . "')";
        $this->dataAccess->executeStatement($sql);
        $itineraryId = $this->dataAccess->insertId();

        foreach ($itineraryPath as $key => $anchor) {
            $anchor = (array) $anchor;
            $sql = "INSERT INTO anchor (id, itinerary_id, sequence, latitude, longitude) 
                    VALUES (0, $itineraryId, $key, " . $anchor["latitude"] . "," . $anchor["longitude"] . ")";
            $this->dataAccess->executeStatement($sql);
        }

        $this->dataAccess->commitTransaction();

        return;

    }
}

class UpdateItineraryActions extends BaseApiAction
{
    protected function performApiCall(array $urlParams, array $queryParams, $body)
    {
        $itineraryId = $urlParams["id"];
        $itinerary = (array) $body;
        $itineraryPath = $itinerary["path"];

        $this->dataAccess->startTransaction();

        // Add the Itinerary
        $sql = "UPDATE itinerary SET NAME = '". $itinerary["name"] . "' WHERE id = ". $itineraryId;
        $this->dataAccess->executeStatement($sql);

        $sql = "DELETE FROM anchor WHERE itinerary_id = ". $itineraryId;
        $this->dataAccess->executeStatement($sql);

        foreach ($itineraryPath as $key => $anchor) {
            $anchor = (array) $anchor;
            $sql = "INSERT INTO anchor (id, itinerary_id, sequence, latitude, longitude) 
                    VALUES (0, $itineraryId, $key, " . $anchor["latitude"] . "," . $anchor["longitude"] . ")";
            $this->dataAccess->executeStatement($sql);
        }

        $this->dataAccess->commitTransaction();

        return;

    }
}