<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function getReservations(){
        $response = ["error" => "", "list" => []];
        $daysHelper = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
        $areas = Area::where("allowed", 1)->get();
        foreach($areas as $area){
            $dayList = explode(",", $area["days"]);
            $dayGroups = [];

            //Add first day
            $lastDay = intval(current($dayList));
            $dayGroups[] = $daysHelper[$lastDay];
            array_shift($dayList);

            //Add others days
            foreach($dayList as $day){

                if(intval($day) != $lastDay+1){
                    $dayGroups[] = $daysHelper[$lastDay];
                    $dayGroups[] = $daysHelper[$day];
                }

                $lastDay = intval($day);
            }

            //Add Last day
            $dayGroups[] = $daysHelper[end($dayList)];

            $dates = "";
            $close = 0;
            foreach($dayGroups as $group){
                if($close === 0){
                    $dates .= $group; 
                } else {
                    $dates .= "-" . $group.",";
                }

                $close = 1 - $close;
            }

            $dates = explode(",", $dates);
            array_pop($dates);

            //Add time
            $start = date("H:i", strtotime($area["start_time"]));
            $end = date("H:i", strtotime($area["end_time"]));
            foreach($dates as $dKey => $dValue){
                $dates[$dKey] .= " ".$start." às ".$end;
            }

            $response["list"][] = [
                "id" => $area["id"],
                "cover" => asset("storage/".$area["cover"]),
                "title" => $area["title"],
                "dates" => $dates
            ];

        }
        return response()->json($response);
    }

    public function setReservation($id, Request $request){
        $response = ["error" => ""];

        $validator = Validator::make($request->all(),[
            "date" => "required|date_format:Y-m-d",
            "time" => "required|date_format:H:i:s",
            "property" => "required"
        ]);
        if(!$validator->fails()){
            $date = $request->input("date");
            $time = $request->input("time");
            $property = $request->input("property");

            $unit = Unit::find($property);
            $area = Area::find($id);

            if($unit && $area){
                $can = true;
                $weekDay = date("w", strtotime($date));
                
                $allowedDays = explode(",", $area["days"]);
                if(!in_array($weekDay, $allowedDays)){
                    $can = false;
                } else {
                    $start = strtotime($area["start_time"]);
                    $end = strtotime("-1 hour", strtotime($area["end_time"]));
                    $revTime = strtotime($time);
                    if($revTime < $start || $revTime > $end){
                        $can = false;
                    }
                }

                //Verify disabled days
                $existingDisabledDay = AreaDisabledDay::where("id_area", $id)->where("day", $date)->count();
                if($existingDisabledDay > 0){
                    $can = false;
                }

                //Verify others reservations iguals
                $existingReservations = Reservation::where("id_area", $id)->where("reservation_date", $date." ".$time)->count();
                if($existingReservations > 0){
                    $can = false;
                }

                if($can){
                    $newErservation = new Reservation();
                    $newErservation->id_unit = $property;
                    $newErservation->id_area = $id;
                    $newErservation->reservation_date = $date." ".$time;
                    $newErservation->save();
                } else {
                    $response["error"] = "Reservation not allowed";
                }
            } else {
                $response["error"] = "Incorrect information";
            }

        } else {
            $response["error"] = $validator->errors()->first();
        }
        return response()->json($response);
    }

    public function getDisabledDates($id){
        $response = ["error" => "", "list" => []];
        
        $area = Area::find($id);
        if($area){
            //default disabled days
            $disabledDays = AreaDisabledDay::where("id_area", $id)->get();
            foreach($disabledDays as $disabledDay){
                $response["list"][] = $disabledDay["day"];
            }
            //Alowed disabled days
            $allowedDays = explode(",", $area["days"]);
            $offDays = [];
            for($q = 0; $q < 7; $q++){
                if(!in_array($q, $allowedDays)){
                    $offDays[] = $q;
                }
            }
            
            //Disabled days +3 months
            $start = time();
            $end = strtotime("+3 months");

            for(
                $current = $start;
                $current < $end;
                $current = strtotime("+1 day", $current)
            ){
                $wd = date("w", $current);
                if(in_array($wd, $offDays)){
                    $response["list"][] = date("Y-m-d", $current);
                }
            }

        } else {
            $response["error"] = "Area not found";
        }

        return response()->json($response);
    }

    public function getTimes($id, Request $request){
        $response = ["error" => "", "list" => []];

        $validator = Validator::make($request->all(), [
            "date" => "required|date_format:Y-m-d"
        ]);

        if(!$validator->fails()){
            $date = $request->input("date");
            $area = Area::find($id);
            if($area){
                $can = true;
                //Verify if disabled day
                $existingDisabledDay = AreaDisabledDay::where("id_area", $id)->where("day", $date)->count();
                if($existingDisabledDay > 0){
                    $can = false;
                }
                //Verify if allowed day
                $allowedDays = explode(",", $area["days"]);
                $weekDay = date("w", strtotime($date));
                if(!in_array($weekDay, $allowedDays)){
                    $can = false;
                }
                if($can){
                    $start = strtotime($area["start_time"]);
                    $end = strtotime($area["end_time"]);

                    $times = [];
                    for(
                        $lastTime = $start;
                        $lastTime < $end;
                        $lastTime = strtotime("+1 hour", $lastTime)
                    ){
                        $times[] = $lastTime;
                    }

                    $timeList = [];
                    foreach ($times as $time) {
                        $timeList[] = [
                            "id" => date("H:i:s", $time),
                            "title" => date("H:i", $time)." - ".date("H:i", strtotime("+1 hour", $time))
                        ];
                    }

                    //Removing reservations
                    $reservations = Reservation::where("id_area", $id)->whereBetween("reservation_date", [
                        $date . " 00:00:00",
                        $date . " 23:59:59"
                    ])->get();

                    $toRemove = [];
                    foreach ($reservations as $reservation) {
                        $time = date("H:i:s", strtotime($reservation["reservation_date"]));
                        $toRemove[] = $time;
                    }

                    foreach ($timeList as $timeItem) {
                        if(!in_array($timeItem["id"], $toRemove)){
                            $response["list"][] = $timeItem;
                        }
                    }

                }
            } else {
                $response["error"] = "Area not found";    
            }
        } else {
            $response["error"] = $validator->errors()->first();
        }

        return response()->json($response);
    }

    public function getMyReservations(Request $request){
        $response = ["error" => ""];
        $property = $request->input("property");
        if($property){
            $unit = Unit::find($property);
            if($unit){
                $reservations = Reservation::where("id_unit", $property)->orderBy("reservation_date")->get();
                foreach ($reservations as $reservation) {
                    $area = Area::find($reservation["id_area"]);
                    $daterev = date("d/m/Y H:i", strtotime($reservation["reservation_date"]));
                    $afterTime = date("H:i", strtotime("+1 hour", strtotime($reservation["reservation_date"])));
                    $daterev .= " - " . $afterTime;
                    $response["list"][] = [
                        "id" => $reservation["id"],
                        "id_area" => $reservation["id_area"],
                        "title" => $area["title"],
                        "cover" => asset("storage/".$area["cover"]),
                        "datereserved" => $daterev
                    ];
                }
            } else {
                $response["error"] = "Property not found.";
            }
        } else {
            $response["error"] = "Property is required.";
        }
        return response()->json($response);
    }

    public function removeReservation($id){
        $response = ["error" => ""];
        $user = auth()->user();
        $reservation = Reservation::find($id);
        if($reservation){
            $unit = Unit::where("id", $reservation["id_unit"])->where("id_owner", $user["id"])->count();
            if($unit > 0){
                Reservation::find($id)->delete();
            } else {
                $response["error"] = "This reservation is not yours.";
            }
        } else {
            $response["error"] = "Reservation already exists."; 
        }

        return response()->json($response);
    }
}
