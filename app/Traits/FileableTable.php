<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

trait FileableTable {
    public function storeFiles(Request $request):array {
        $tableModel = $this->getFields();
        $fileFields = $tableModel->getFileFields();
        $files = [];
        foreach($fileFields as $fieldName) {
            if ($request->hasFile($fieldName)) {
                $savePath = $request->file($fieldName)->store('');
                if ($savePath) {
                    $files[$fieldName] = $savePath;
                }
            }
        }
        return $files;
    }

    public function updateFiles(Request $request):array {
        $tableModel = $this->getFields();
        $fileFields = $tableModel->getFileFields();
        $files = [];
        $attributes = $this->getAttributes();
        foreach($fileFields as $fieldName) {
            if (strtolower($request->method())=='patch') {
                if ($request->hasFile($fieldName)) {
                    // clear origin files
                    $file = $attributes[$fieldName];
                    if (!is_null($file) && Storage::exists($file)) {
                        Storage::delete($file);
                    }
                    // upload new files
                    $savePath = $request->file($fieldName)->store('');
                    if ($savePath) {
                        $files[$fieldName] = $savePath;
                    }
                }
            } else {
                $file = $attributes[$fieldName];
                if (!is_null($file) && Storage::exists($file)) {
                    Storage::delete($file);
                }
                if ($request->hasFile($fieldName)) {
                    $savePath = $request->file($fieldName)->store('');
                    if ($savePath) {
                        $files[$fieldName] = $savePath;
                    }
                } else {
                    $files[$fieldName] = null;
                }
            }
        }
        return $files;
    }

    public function deleteFiles():void {
        $tableModel = $this->getFields();
        $fileFields = $tableModel->getFileFields();
        $attributes = $this->getAttributes();
        foreach($fileFields as $fieldName) {
            $file = $attributes[$fieldName];
            if (!is_null($file) && Storage::exists($file)) {
                Storage::delete($file);
            }
        }
    }

    public function clearUpload(array $files) {
        foreach ($files as $uploadFile) {
            if (Storage::exists($uploadFile)) {
                Storage::delete($uploadFile);
            }
        }
    }
}
