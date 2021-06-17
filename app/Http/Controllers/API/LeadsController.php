<?php

namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;

class LeadsController extends BaseController
{
    public function findLeadsFromYelp()
    {
        $rules = [
			'term' => 'required|string|min:2',
            'location' => 'required|string|min:2',
            'page' => 'nullable|integer|min:1',
            'sr_no' => 'nullable|integer|min:1'
		];

		$validation = $this->validated($rules, $this->request->all());

		if ($validation->fails()) 
        {
			return $this->sendError($validation->errors(), 9001, 422);
		}

        $page = isset($this->request->page)  ? $this->request->page : 0;

        $sr_no = $this->request->sr_no ? $this->request->sr_no : 1;
        
        $offset =  $page + 50; 

        $response = $this->yelpApi->searchBusinesses($offset);
        
        $leads = [];

        if($response->status() === 200 && $response->getOriginalContent()['code'] === 200)
        {
            $data = $response->getOriginalContent()['data'];

            $total = $data->total;

            // $pagination = $this->helper->pagination($total, 1, 50);

            // $sr_no = $this->helper::GenerateSrNo(1, 10);
            
            foreach($data->businesses as $lead)
            {
                $leads[] = [
                    'sr_no' => $sr_no,
                    'sid' => $lead->id,
                    'name' => $lead->name,
                    'image_url' => $lead->image_url,
                    'display_phone' => $lead->display_phone,
                    'phone' => $lead->phone,
                    'url' => $lead->url
                ];
                
                $sr_no++;
            }

        }
        
        return $this->sendResponse([
            'total' => $total,
            'items' => $leads,
            'page' => $offset,
            'sr_no' => $sr_no
        ]);
    }

    private function checkLeadsExists($leads)
    {

    }
}
