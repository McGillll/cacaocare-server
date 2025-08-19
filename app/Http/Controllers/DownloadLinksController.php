<?php

namespace App\Http\Controllers;

use App\Http\Requests\DownloadLinksStoreRequest;
use App\Http\Requests\DownloadLinksUpdateRequest;
use App\Models\DownloadLinks;
use Exception;
use Illuminate\Support\Facades\DB;

class DownloadLinksController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $download_link = DB::table('download_links')
                ->select('*')
                ->orderByRaw('created_at DESC')
                ->get();
        return response()->json([
            'data' => $download_link
        ],200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DownloadLinksStoreRequest $request)
    {
        $downloadLink = new DownloadLinks();
        $downloadLink->download_link = $request->download_link;
        $downloadLink->version = $request->version;
        $downloadLink->description = $request->description;

        $downloadLink->save();

        return response()->json([
            'data' => $downloadLink
        ], 200);
    }

    /**
     * Display the latest resource.
     */
    public function show()
    {
    }

    public function getLatest()
    {
        $downloadLink = DownloadLinks::latest('created_at')->first();
        return response()->json([
            'data' => $downloadLink
        ], 200);
    }

    public function getLatestDate(){
        $downloadLink = DownloadLinks::latest('created_at')->select('created_at')->first();
        return response()->json([
            'data' => $downloadLink
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DownloadLinksUpdateRequest $request, int $id)
    {
        $downloadLink = DownloadLinks::find($id);
        if(!$downloadLink){
            return response()->json([
                'error' => true,
                'message' => 'Data not found'
            ], 404);
        }

        if($request->download_link){
            $downloadLink->download_link = $request->download_link;
        }
        if($request->version){
            $downloadLink->version = $request->version;
        }
        if($request->description){
            $downloadLink->description = $request->description;
        }

        $downloadLink->save();

        return response()->json([
            'data' => $downloadLink
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
            $download_link = DownloadLinks::find($id);
            if(!$download_link){
                return response()->json([
                    'error' => true,
                    'message' => 'Data not found'
                ], 404);
            }
            $result = $download_link->delete();

            return response()->json([
                'data' => $result,
                'message' => "Data deleted successfully"
            ], 200);
    }
}
