<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class ApiUtilisateurController extends AbstractController
{
    #[Route('/api/utilisateur', name: 'app_api_utilisateur')]
    public function index(): Response
    {
        return $this->render('api_utilisateur/index.html.twig', [
            'controller_name' => 'ApiUtilisateurController',
        ]);
    }

    #[Route("/Allusers", name: "listuserjson")]
    //* Dans cette fonction, nous utilisons les services NormlizeInterface et StudentRepository, 
    //* avec la méthode d'injection de dépendances.
    public function getUsers(UserRepository $repo, SerializerInterface $serializer)
    {
        $users = $repo->findAll();
        //* Nous utilisons la fonction normalize qui transforme le tableau d'objets 
        //* students en  tableau associatif simple.
        // $usersNormalises = $normalizer->normalize($users, 'json', ['groups' => "user"]);

        // //* Nous utilisons la fonction json_encode pour transformer un tableau associatif en format JSON
        // $json = json_encode($usersNormalises);

        $json = $serializer->serialize($users, 'json', ['groups' => "user"]);

        //* Nous renvoyons une réponse Http qui prend en paramètre un tableau en format JSON
        return new Response($json);
    }

    #[Route("/UserId/{id}", name: "userjson")]
    public function UserId($id, NormalizerInterface $normalizer, UserRepository $repo)
    {
        $user = $repo->find($id);
        $userNormalises = $normalizer->normalize($user, 'json', ['groups' => "user"]);
        return new Response(json_encode($userNormalises));
    }


    #[Route("addUserJSON/new", name: "addUserJSON")]
    public function addUserJSON(Request $req,   NormalizerInterface $Normalizer, UserPasswordHasherInterface $userPasswordHasher)
    {

        $em = $this->getDoctrine()->getManager();
        $user = new User();
        $user->setEmail($req->get('email'));
        $user->setUsername($req->get('username'));
        $user->setNom($req->get('nom'));
        $user->setPrenom($req->get('prenom'));
        $password = $req->get('password');
        $PlainPassword = $userPasswordHasher->hashPassword(
            $user,
            $password
        );
        $user->setPassword($PlainPassword);
        $user->setNumTel($req->get('num_tel'));
        $date_naissance = $req->get('date_naissance');
        $user->setDateNaissance(new \DateTimeImmutable($date_naissance));
        $user->setRoles(['ROLE_GAMER']);
        $em->persist($user);
        $em->flush();

        $jsonContent = $Normalizer->normalize($user, 'json', ['groups' => 'user']);
        return new Response(json_encode($jsonContent));
    }

    #[Route("updateUserJSON/{id}", name: "updateUserJSON")]
    public function updateStudentJSON(Request $req, $id, NormalizerInterface $Normalizer, UserPasswordHasherInterface $userPasswordHasher)
    {

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($id);
        // Get the current date from the database
        $currentDate = $user->getDateNaissance();
        $currentEmail = $user->getEmail();
        $currentUsername = $user->getUsername();
        $currentNumTel = $user->getNumTel();
        $user->setEmail($currentEmail);
        $user->setUsername($currentUsername);
        $user->setNom($req->get('nom'));
        $user->setPrenom($req->get('prenom'));
        $password = $req->get('password');
        if ($password !== null) {
            $PlainPassword = $userPasswordHasher->hashPassword(
                $user,
                $password
            );
            $currentPass = $user->getPassword();
            $user->setPassword($currentPass);
        }
        $user->setNumTel($req->get('num_tel'));
        //$date_naissance = $req->get('date_naissance');
        if ($currentDate !== null) {
            $user->setDateNaissance($currentDate);
        }
        $em->flush();

        $jsonContent = $Normalizer->normalize($user, 'json', ['groups' => 'user']);
        return new Response("User updated successfully " . json_encode($jsonContent));
    }


    #[Route("deleteUserJSON/{id}", name: "deleteUserJSON")]
    public function deleteUserJSON($id, NormalizerInterface $Normalizer)
    {

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($id);
        $em->remove($user);
        $em->flush();
        $jsonContent = $Normalizer->normalize($user, 'json', ['groups' => 'user']);
        return new Response("User deleted successfully " . json_encode($jsonContent));
    }

    #[Route("/signin", name: "signin", methods: ["POST", "GET"])]
    public function login(Request $request, AuthenticationUtils $authenticationUtils, UserRepository $userRepository): JsonResponse
    {
        $username = $request->get('username');
        $password = $request->get('password');

        // Find the user by username
        $user = $userRepository->findOneBy(['username' => $username]);

        /*if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Check if the password is valid
         if (!password_verify($password, $user->getPassword())) {
            return new JsonResponse(['message' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        } 

        // authentication succeeded, return success response
        return new JsonResponse(['message' => 'Authentication succeeded']);*/
        if ($user) {
            if (password_verify($password, $user->getPassword())) {
                $serializer = new Serializer([new ObjectNormalizer()]);
                $formatted = $serializer->normalize($user);
                return new JsonResponse($formatted);
            } else {
                return new Response("password not found");
            }
        } else {
            return new Response("user not found");
        }
    }

    #[Route("/signupUser", name: "signupUserJSON")]
    public function signupUserJSON(Request $req,   NormalizerInterface $Normalizer, UserPasswordHasherInterface $userPasswordHasher)
    {

        $em = $this->getDoctrine()->getManager();
        $user = new User();
        $email = $req->get('email');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new Response("email invalid.");
        }
        $user->setEmail($email);
        $user->setUsername($req->get('username'));
        $user->setNom($req->get('nom'));
        $user->setPrenom($req->get('prenom'));
        $password = $req->get('password');
        $PlainPassword = $userPasswordHasher->hashPassword(
            $user,
            $password
        );
        $user->setPassword($PlainPassword);
        $numTel = (int)$req->get('num_tel');
        if (strlen((string)$numTel) !== 8) {
            return new Response("your phone number must have a length of 8 numbers.");
        }
        $user->setNumTel($numTel);
        $date_naissance = $req->get('date_naissance');
        $user->setDateNaissance(new \DateTimeImmutable($date_naissance));
        $user->setRoles(['ROLE_GAMER']);
        $user->setIsVerified(true);
        $user->setIsBanned(false);
        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return new JsonResponse("Account is cretaed", 200);
        } catch (\Exception $ex) {
            return new Response("exception" . $ex->getMessage());
        }
        $em->persist($user);
        $em->flush();

        $jsonContent = $Normalizer->normalize($user, 'json', ['groups' => 'user']);
        return new Response(json_encode($jsonContent));
    }
}
