<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Facebook\Facebook;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Date;

class HomeController extends Controller
{
    /**
     * @Route("/home", name="home")
     */
    public function indexAction(Request $request)
    {
        return $this->render('default/home.html.twig');
    }

    /**
     * @Route("/user/save", name="save")
     */
    public function saveAction(Request $request){
        $userId = $request->request->get('userID');
        $accessToken = $request->request->get('accessToken');
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(["userId"=>$userId]);
        $fb = new Facebook([
            'app_id' => '364179270683890',
            'app_secret' => '9741e03725787d6075c316d617b7ce26',
            'default_graph_version' => 'v2.10',
        ]);
        $response = $fb->get('/me?fields=about,education,age_range,birthday,email,name,picture{height,width,url,is_silhouette},gender,hometown', "{$accessToken}");
        if($user==null){
            $user = new User();
            $user->setUserId($userId);
            $user->setPictureUrl($response->getGraphUser()->getPicture());
            $user->setBirthDate('2012-10-23');
            $user->setHomeTown($response->getGraphUser()->getHometown());
            $user->setEmail($response->getGraphUser()->getEmail());
            $user->setGender($response->getGraphUser()->getGender());
        }
        $user->setName($response->getGraphUser()->getName());
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return new Response($user->getUserId());

    }



}
