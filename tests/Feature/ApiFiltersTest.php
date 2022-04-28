<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Testing\TestResponse;
use App\Models\Message;

class ApiFiltersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * seed before testing
     *
     * @var bool
     */
    protected $seed = true;

    /**
     * test like condition
     *
     * @return void
     */
    public function testLikeConditions() {
        $msg = Message::where('number','like','%+%')->get()->random();
        $messageLines = explode(' ',$msg->message);
        $url = "messages?limit=5&filter=".rawurlencode("message like \"".$messageLines[count($messageLines)-1]." ".$messageLines[0]."\"");

        $messages = Message::where('message','like',"%".$messageLines[count($messageLines)-1]."%")
            ->where('message','like',"%".$messageLines[0]."%")
            ->get();

        $messagesCount = $messages->count();
        $response = $this->request($url);
        // dd($messagesCount, $url, $response);
        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) use ($messagesCount) {
                $json->where('count',$messagesCount)
                    ->where('error', null)
                    ->where('is_error', false)
                    ->has('data', $messagesCount);
            });
    }
    /**
     * test ne condition
     *
     * @return void
     */
    public function testNeConditions() {
        $msg = Message::where('number','like','%+%')->get()->random();
        $msg_number = $msg->number;
        $url = "messages?limit=0&filter=".rawurlencode("number ne $msg_number");
        $messages = Message::where('number','<>',$msg_number)->get();
        $messagesCount = $messages->count();
        $response = $this->request($url);
        // dd($messagesCount, $url, $response);
        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) use ($messagesCount) {
                $json->where('count',$messagesCount)
                    ->where('error', null)
                    ->where('is_error', false)
                    ->has('data', $messagesCount);
            });
    }
    /**
     * test eq condition
     *
     * @return void
     */
    public function testOrAndOperands() {
        $messages = Message::get()->random(2);
        $url = "messages?limit=0&filter=".rawurlencode("number eq ".$messages[0]->number." or id eq ".$messages[1]->id."");
        $messages = Message::where('number',$messages[0]->number)->orWhere('id',$messages[1]->id)->get();
        $messagesCount = $messages->count();
        $response = $this->request($url);
        // dd($messagesCount, $url, $response);
        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) use ($messagesCount) {
                $json->where('count',$messagesCount)
                    ->where('error', null)
                    ->where('is_error', false)
                    ->has('data', $messagesCount);
            });
    }
    /**
     * test in & ni condition
     *
     * @return void
     */
    public function testNiInConditions() {
        $messages = Message::get()->random(7);
        $exceptIds = [];
        $validIds = [];
        $i = 0;
        foreach($messages as $msg) {
            if (in_array($i,[2,5])) $exceptNumbers[] = $msg->id;
            if ($i != 4) $validIds[] = $msg->id;
            $i++;
        }

        $url = "messages?limit=0&filter=".rawurlencode("id ni ".json_encode($exceptIds)." and id in ".json_encode($validIds)." and cost in ".$messages[4]->cost." and is_translit in ".json_encode(['on'])."");
        $requestMessages = Message::whereIn('id',$validIds)
            ->whereNotIn('number',$exceptNumbers)
            ->whereIn('is_translit',[1])
            ->whereIn('cost',[$messages[4]->cost])
            ->get();
        $messagesCount = $requestMessages->count();
        $response = $this->request($url);

        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) use ($messagesCount) {
                $json->where('count',$messagesCount)
                    ->where('error', null)
                    ->where('is_error', false)
                    ->has('data', $messagesCount);
            });
    }
    /**
     * test gt & gte condition
     *
     * @return void
     */
    public function testGtGteConditions() {
        $avgCost = Message::get()->avg('cost');
        $lowCost = Message::get()->where('cost','<',$avgCost)->max('cost');

        $url = "messages?limit=0&filter=".rawurlencode("cost gt ".$avgCost." or cost gte $lowCost");
        $requestMessages = Message::where('cost','>',$avgCost)->orWhere('cost','>=',$lowCost)->get();
        $messagesCount = $requestMessages->count();
        $response = $this->request($url);

        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) use ($messagesCount) {
                $json->where('count',$messagesCount)
                    ->where('error', null)
                    ->where('is_error', false)
                    ->has('data', $messagesCount);
            });
    }
    /**
     * test lt & lte condition
     *
     * @return void
     */
    public function testLtLteConditions() {
        $avgCost = Message::get()->avg('cost');
        $expCost = Message::where('cost','>',$avgCost)->get()->random()->cost;

        $url = "messages?limit=0&filter=".rawurlencode("cost lt ".$avgCost." or cost lte $expCost");
        $requestMessages = Message::where('cost','<',$avgCost)->orWhere('cost','<=',$expCost)->get();
        $messagesCount = $requestMessages->count();
        $response = $this->request($url);

        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) use ($messagesCount) {
                $json->where('count',$messagesCount)
                    ->where('error', null)
                    ->where('is_error', false)
                    ->has('data', $messagesCount);
            });
    }
    /**
     * test cast boolean type
     *
     * @return void
     */
    public function testCastBooleanType() {

        $url = "messages?limit=0&filter=".rawurlencode("is_translit eq no or is_translit ne wrong_andbor_bool_type or is_translit ne yes or is_translit ne true");
        $requestMessages = Message::where('is_translit',0)->get();
        // dd($url);
        $messagesCount = $requestMessages->count();
        $response = $this->request($url);
// dd($url, $messagesCount, $response);
        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) use ($messagesCount) {
                $json->where('count',$messagesCount)
                    ->where('error', null)
                    ->where('is_error', false)
                    ->has('data', $messagesCount);
            });

    }
    /**
     * test cast date types
     *
     * @return void
     */
    public function testCastDateTypes() {

        $msg = Message::get()->random();

        $url = "messages?limit=0&filter=".rawurlencode("created_at eq ".$msg->created_at." and updated_at eq ".$msg->created_at." or updated_at ne wrong_format");
        $requestMessages = Message::whereDate('created_at',$msg->created_at)->whereDate('updated_at',$msg->updated_at)->get();
        $messagesCount = $requestMessages->count();
        $response = $this->request($url);
        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) use ($messagesCount) {
                $json->where('count',$messagesCount)
                    ->where('error', null)
                    ->where('is_error', false)
                    ->has('data', $messagesCount);
            });
    }


    /**
     * return admin user for request
     *
     * @return User
     */
    protected function adminUser(): User
    {
        return User::whereNotNull('email_verified_at')->first();
    }


    /**
     * request get
     *
     * @param  string $url
     * @return TestResponse
     */
    protected function request($url):TestResponse
    {
        return $this->actingAs($this->adminUser())->getJson("/api/v1/$url");
    }

}
