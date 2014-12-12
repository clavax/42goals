<?php
import('base.controller.BaseApi');

class SnappcloudRequsitionApi extends BaseApi 
{
    public function handlePostDefault(array $request = array())
    {
        $Users = new PrimaryTable('users', 'id');
        $user = array(
            'name' => $request['firstname'] . ' ' . $request['lastname'],
            'email' => $request['email'],
            'login' => 'ts:' . base_convert(time(), 10, 36) . base_convert(uniqid(), 10, 36),
            'password' => md5('pepper' . $request['password']),
            'status' => 'active',
            'registered' => date('Y-m-d')
        );
        if ($request['sku'] == 'premium') {
            $user['paid_till'] = date('Y-m-d', strtotime('+30 days'));
        }
        if ($request['demo'] == 'False') {
            $user_id = $Users->insert($user);
            file::mkdir($this->PTH->icons . $user['login'], 0775);
        } else {
            $user_id = hexdec(substr(uniqid(), 0, 6));
        }
        
        $Snappcloud = new PrimaryTable('snappcloud', 'id');
        $hash = md5(uniqid(null, true));
        $order_id = $Snappcloud->insert(array(
            'user' => $user_id,
            'order_id' => $request['order_id'],
            'data' => json::encode($request),
            'date' => date('Y-m-d H:i:s'),
            'hash' => $hash
        ));
        
        $response = array(
            'Order' => array(
                'SupplierCode' => $order_id
            ),
            'Customer' => array(
                'SupplierCode' => $user_id
            ),
            'Service' => array(
                'DownloadUrl' => "http://{$this->ENV->host}{$this->ENV->home}/login/snappcloud/{$order_id}-{$hash}/"
            )
        );
        
        $this->Error->log(describe($request));
        $this->Error->log(describe($response));
        return $this->respondOk($response);
    }
}
?>