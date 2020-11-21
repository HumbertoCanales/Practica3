<?php

namespace App\Http\Controllers\ApiFunctions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NYTimesController extends Controller
{
    private $api_key = "GAJSIGtK2gjewWzalK3wds7NRpv3VkH4";
    public function __construct(){
        $this->middleware('auth:sanctum');
    }
    

    public function search(Request $request){
        $url = "https://api.nytimes.com/svc/search/v2/articlesearch.json";
        $response = Http::get($url, [
            'q' => $request->search_query,
            'api-key' => $this->api_key
        ]);
        $docs = $response->json()['response']['docs'];
        if($docs){
            foreach ($docs as $doc) {
                $final_res[] = (["abstract" => $doc['abstract'], "web_url" => $doc['web_url']]); 
             }
             return $final_res;
        }
        return response()->json(['message' => "No results found"], 404);
        
    }

    public function mostPopular(Request $request, $period){
        if($period == 1 || $period == 7 || $period == 30){
            $url = "https://api.nytimes.com/svc/mostpopular/v2/emailed/".$period.".json";
            $response = Http::get($url, [
                'api-key' => $this->api_key
            ]);
            $results = $response->json()['results'];
            foreach ($results as $result) {
               $final_res[] = (["url" => $result['url']]); 
            }
            return $final_res;
        }
        return response()->json(['message' => "Invalid period. The period must be: 1, 7 or 30 (days)."], 422);
    }

    public function books(Request $request){
        $url = "https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json";
        $response = Http::get($url, [
            'api-key' => $this->api_key
        ]);
        $results = $response->json()['results'];
        foreach ($results as $result) {
           $final_res[] = (["title" => $result['title'],
                            "description" => $result['description'],
                            "author" => $result['author'],
                            "publisher" => $result['publisher']]); 
        }
        return $final_res;
    }
}
