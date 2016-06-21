<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Application\Database\UserTable;
use Application\Model\User;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Http\Response;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;


use Instagram\Auth;
use Instagram\Instagram;

class IndexController extends AbstractActionController
{
    private $auth;

    /**
     * IndexController constructor.
     *
     * Creates an instance of the Auth class
     *   - it takes an array of settings for OAuth.
     */
    public function __construct()
    {
        // the client ID and secret are obtained when you register as a developer
        // the redirect_uri is the URL you want instagram to redirect your user to, after they login
        // the redirect_uri is also set upon registration as a developer. make sure this matches
        $this->auth = new Auth([
            'client_id'     => 'get_from_instagram',
            'client_secret' => 'get_from_instagram',
            'redirect_uri'  => 'http://localhost:8000/authorize',   // when you're live, this will be your full domain instead
            'scope'         => ['public_content']   // not used any more due to recent changes. other APIs may have this though
        ]);
    }

    /**
     * This is the action that redirects the user from your website,
     * to the instagram authorization page.
     *
     * They login and click authorize to allow your website to act on their behalf.
     */
    public function loginAction()
    {
        // this one simple call will take care of the redirect for us
        $this->auth->authorize();
    }

    /**
     * This is the redirect_uri that was specified for the OAuth handshake.
     *
     * When the user is redirected back here, a 'code' is provided by instagram.
     * Your website needs to use this code to request for an Access Token.
     *
     * @param code string GET Query param that has the 'code' to request for an Access Token
     * @return JsonModel
     * @throws \Instagram\Core\ApiException
     */
    public function authorizeAction()
    {
        $data = [];

        // grab the code from the GET params - it's provided in the URI as ?code=
        $code = $this->getRequest()->getQuery('code');

        // use the code to retrieve an access token from instagram, this is part of standard OAuth handshake.
        // you should store this token in a database so that future transactions
        // that you want to make on behalf of the user you can just use this token.
        // the code can be discarded - it's one time use
        $token = $this->auth->getAccessToken($code);

        // now that we have a token, setup the SDK with the client ID and the access token
        $instagram = new Instagram();
        $instagram->setClientID('get_from_instagram');
        $instagram->setAccessToken($token);

        // now use the SDK to get the current user (based on that token)
        $user = $instagram->getCurrentUser();

        // and through that user, grab their media
        $media = $user->getMedia();

        // the SDK returns all the user's media in a collection for us
        foreach ($media as $picture)
        {
            // loop through the collection and assign each images property to the data array
            $data[] = $picture->images;
        }

        // the data array is sent to the view layer
        // in our example, we send JSON.
        // You can use a standard ViewModel to send it to a phtml file too - perhaps a gallery page of sorts
        return new JsonModel($data);
    }

    /* ************************************************ */
    /*  rest of this is from the lab 6 solution
    /* ************************************************ */

    public function indexAction()
    {
        return new ViewModel();
    }


    public function usersAction()
    {
        $people = [];
        $userTable = new UserTable('comp2920', 'root', 'coleen');

        try {
            $users = $userTable->getUsers();
            foreach ($users as $user)
            {
                $userModel = new User();
                $userModel->setFullName($user['users_firstname'] . ' ' . $user['users_lastname']);
                $userModel->setAge($user['users_age']);

                $people[] = $userModel->getArray();
            }
        }
        catch(\Exception $e) {
            echo $e->getMessage();
        }

        return new JsonModel($people);
    }
}
