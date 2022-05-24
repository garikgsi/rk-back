<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Facades\Table;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use App\Models\Operation;
use Illuminate\Support\Facades\Storage;

class FilesTest extends TestCase
{

    /**
     * test get all file fields
     *
     * @return void
     */
    public function testGetTableFileFields() {
        $fileFields = Table::use('operations')->getModel()->getFields()->getFileFields();
        $this->assertSame($fileFields[0],'image');
    }

    /**
     * test delete file when save error
     *
     * @return void
     */
    public function testDeleteUploadedFileWhenSaveError() {
        $file = UploadedFile::fake()->image("test.jpg");
        $data = [
            'amount' => 200,
            'image' => $file,
        ];
        $storageFile = $file->hashName();
        $url = "operations";
        $response = $this->request($url, $data);
        $response->assertStatus(422)
            ->assertJson([
                'is_error' => true,
            ]);
        Storage::assertMissing($storageFile);
    }

    /**
     * test load and create file on local fs on store new record
     *
     * @return string
     */
    public function testCreateFile():array {
        $storageFiles = [];
        for($i=0;$i<5; $i++){
            $file = UploadedFile::fake()->image("test$i.jpg");
            $data = [
                'date_operation' => date('Y-m-d'),
                'comment' => "testUploadFile$i",
                'price' => 100,
                'quantity' => 2,
                'amount' => 200,
                'image' => $file,
            ];
            $storageFile = $file->hashName();
            $url = "operations";
            $response = $this->request($url, $data);
            $response->assertStatus(201)
                ->assertJson([
                    'is_error' => false,
                    'error' => null,
                    'data' => array_merge($data, ['image' => env('APP_URL').'/file/'.$storageFile])]
                );
            Storage::assertExists($storageFile);
            $storageFiles[] = $storageFile;
        }
        return $storageFiles;
    }

    /**
     * test remove file from fs when file updated
     *
     * @depends testCreateFile
     * @return array [id, file]
     */
    public function testTestDeleteFileOnUpdateRow($storageFiles):array {
        $storageFile = $storageFiles[0];
        $operation = Operation::where('image', 'like', "%$storageFile%")->first();
        $file = UploadedFile::fake()->image('test2.jpg');
        $data = [
            'image' => $file,
        ];
        $newFile = $file->hashName();
        $url = "operations/$operation->id";
        $response = $this->request($url, $data, 'patchJson');
        $response->assertStatus(200)
            ->assertJson([
                'is_error' => false,
                'error' => null,
                'data' => array_merge($operation->toArray(), ['image' => env('APP_URL').'/file/'.$newFile])]
            );
            Storage::assertMissing($storageFile);
            Storage::assertExists($newFile);
            return ['id'=>$operation->id, 'file'=>$newFile];
    }

    /**
     * test exists file on fs when patch row and file field is empty
     *
     * @depends testTestDeleteFileOnUpdateRow
     * @return void
     */
    public function testExistedFileOnPatchRowAndEmptyFile($fileData) {
        $operation = Operation::find($fileData['id']);
        $data = [
            'comment' => 'someAnotherComment',
        ];
        $url = "operations/".$fileData['id'];
        $response = $this->request($url, $data, 'patchJson');
        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('is_error', false)
                    ->where('error', null)
                    ->where('data.id',$operation->id)
                    ->where('data.image',env('APP_URL').'/file/'.$fileData["file"])
                    ->etc()
            );
            Storage::assertExists($fileData["file"]);
    }

    /**
     * test exists file on fs when patch row and file field is empty
     *
     * @depends testTestDeleteFileOnUpdateRow
     * @return void
     */
    public function testDeleteFileOnPutRowAndEmptyFile($fileData) {
        $operation = Operation::find($fileData['id']);
        $data = [
            'date_operation' => date('Y-m-d'),
            'comment' => 'anotheTestUploadFile',
            'price' => 100,
            'quantity' => 2,
            'amount' => 200,
        ];
        $url = "operations/".$fileData['id'];
        $response = $this->request($url, $data, 'putJson');
        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('is_error', false)
                    ->where('error', null)
                    ->where('data.id',$operation->id)
                    ->where('data.image','')
                    ->etc()
            );
            Storage::assertMissing($fileData["file"]);
    }

    /**
     * test remove file from fs when delete record
     *
     * @depends testCreateFile
     * @param array
     * @return void
     */
    public function testDeleteFileOnDeleteRow($storageFiles) {
        $storageFile = $storageFiles[1];
        $operation = Operation::where('image', 'like', "%$storageFile%")->first();
        Storage::assertExists($storageFile);
        $url = "operations/".$operation->id;
        $response = $this->request($url, [], 'deleteJson');
        $response->assertStatus(204);
        Storage::assertMissing($storageFile);
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
     * request put/patch
     *
     * @param  string $url
     * @param  array $data
     * @return TestResponse
     */
    protected function request($url, $data, $method="postJson"):TestResponse
    {
        if ($method=='deleteJson') {
            return $this->actingAs($this->adminUser())->$method("/api/v1/$url");
        } else {
            return $this->actingAs($this->adminUser())->$method("/api/v1/$url", $data ?: []);
        }
    }
}
