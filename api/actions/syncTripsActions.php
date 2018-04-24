<?php
class SyncTripsAction extends BaseApiAction
{
    protected function performApiCall(array $urlParams, array $queryParams, $body)
    {
        $dataAccess = DataAccessManagerFactory::getInstance();
        $tripsLogs = (array) $body;

        if(sizeof($tripsLogs) == 0) {
            return;
        }

        $tripName = $tripsLogs[0]->tripId;
        $tripId = $this->handleNewTripName($dataAccess, $tripName);
        foreach ($tripsLogs as $key => $tripsLog) {
            if($tripName != $tripsLog->tripId) {
                $tripName = $tripsLog->tripId;
                $tripId = $this->handleNewTripName($dataAccess, $tripName);
            }
            $occurred = $tripsLog->occurred;
            $sql = "INSERT INTO trip_log (ID, trip_id, latitude, longitude, occurred) 
                    VALUES (0, '$tripId', $tripsLog->latitude, $tripsLog->longitude, '$occurred')";

            $this->dataAccess->executeStatement($sql);
        }

        return;
    }

    private function handleNewTripName($dataAccess, $tripName)
    {
        $tripId = 0;
        $existingTripId = $dataAccess->queryForField("SELECT IFNULL(ID, 0) from trip WHERE name = '$tripName'");
        if ($existingTripId != NULL) {
            $tripId = $existingTripId;
        } else {
            // new Trop
            $sql = "INSERT INTO trip (id, name) VALUES ($tripId, '$tripName')";
            $dataAccess->executeStatement($sql);
            $tripId = $dataAccess->insertId();
        }
        return $tripId;
    }
}
