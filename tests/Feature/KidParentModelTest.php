<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\KidParent;
use App\Models\Kid;
use App\Models\User;

class KidParentModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * seed before testing
     *
     * @var bool
     */
    protected $seed = true;


    /**
     * test relation with Kid Model
     *
     * @return void
     */
    public function testKidRelation()
    {
        $kidParent = KidParent::whereNotNull('kid_id')->get()->random();
        $kid = $kidParent->kid;
        $this->assertTrue($kid instanceof Kid);
        $this->assertSame($kid->toArray(), Kid::find($kidParent->kid_id)->toArray());
    }

    /**
     * test relation with User Model
     *
     * @return void
     */
    public function testUserRelation()
    {
        $kidParent = KidParent::whereNotNull('user_id')->get()->random();
        $user = $kidParent->user;
        $this->assertTrue($user instanceof User);
        $this->assertSame($user->toArray(), User::find($kidParent->user_id)->toArray());
    }
}
