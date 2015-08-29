<?php

namespace App\Http\Controllers;

use App\Challenge;
use App\Entry;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use JWTAuth;

class EntryController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth', ['only' => 'newEntry']);
    }

    public function getDetails( $challengeType, $challenge_id, $entry_id)
    {
        if(env('APP_DATAMODE') == "dummy"){

            $result = array();

            $dummy = (object)array(
                'id' => 1,
                'name' => 'Dummy Entry #1',
                'category' => 'weekly',
                'img' => 'filename',
                'desc' => 'This is a dummy description',
                'userName' => 'Simon',
                'userPic' => 'simon.jpg',
                'likes' => 10
            );
            array_push($result, $dummy);

            return $result;

        } else {
            $challenge = Challenge::findOrFail($challenge_id);
            $entry = Entry::findOrFail($entry_id);

            $user = $entry->user;

            unset($entry->challenge_id);
            unset($entry->created_at);
            unset($entry->updated_at);
            unset($entry->user);
            $entry->userPic = $user->userPic;
            $entry->userName = $user->name;
            $entry->category = ($challenge->isWeekly) ? 'weekly' : 'monthly';
            $entry->likes = $entry->likesCount();

            return $entry;
        }
    }

    public function newEntry(Request $request, $challengeType, $challenge_id){
        $challenge = Challenge::findOrFail($challenge_id);
        if($challenge->getEndDate() <= Carbon::now()){
            abort(403, 'Challenge is already expired');
        }else{
            $user = JWTAuth::parseToken()->authenticate();
            $newEntry = $request->all();
            $newEntry['challenge_id'] = $challenge_id;
            $newEntry['user_id'] = $user->id;
            return Entry::create($newEntry);
        }
    }
}