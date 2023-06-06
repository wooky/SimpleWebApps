<?php

declare(strict_types=1);

namespace SimpleWebApps\Controller;

use SimpleWebApps\Entity\User;
use SimpleWebApps\Form\LoginFormType;
use SimpleWebApps\Form\ProfileFormType;
use SimpleWebApps\Form\RegistrationFormType;
use SimpleWebApps\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use function assert;
use function is_string;

#[Route(name: 'auth_')]
class AuthController extends AbstractController
{
  #[Route('/register', name: 'register', methods: ['GET', 'POST'])]
  public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserRepository $userRepository): Response
  {
    $user = new User();
    $form = $this->createForm(RegistrationFormType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      // encode the plain password
      $user->setPassword(
        $userPasswordHasher->hashPassword(
          $user,
          (string) $form->get('plainPassword')->getData()
        )
      );

      $userRepository->save($user, true);
      // do anything else you need here, like send an email

      return $this->redirectToRoute('index');
    }

    return $this->render('auth/register.html.twig', [
        'registrationForm' => $form,
    ]);
  }

  #[Route('/login', name: 'login', methods: ['GET', 'POST'])]
  public function login(AuthenticationUtils $authenticationUtils): Response
  {
    $form = $this->createForm(LoginFormType::class, options: [
        LoginFormType::LAST_USERNAME => $authenticationUtils->getLastUsername(),
    ]);

    $error = $authenticationUtils->getLastAuthenticationError();
    if ($error) {
      $form->addError(new FormError($error->getMessageKey()));
    }

    return $this->render('auth/login.html.twig', [
        'form' => $form,
    ]);
  }

  #[Route('/profile', name: 'profile', methods: ['GET', 'POST'])]
  public function profile(
    Request $request,
    UserPasswordHasherInterface $userPasswordHasher,
    UserRepository $userRepository,
    #[CurrentUser] User $user,
  ): Response {
    $form = $this->createForm(ProfileFormType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      // encode the plain password
      $plainPassword = $form->get('plainPassword')->getData();
      assert(is_string($plainPassword) || null === $plainPassword);
      if ($plainPassword) {
        $user->setPassword(
          $userPasswordHasher->hashPassword(
            $user,
            $plainPassword
          )
        );
      }

      $userRepository->save($user, true);

      return $this->redirectToRoute('auth_profile');
    }

    return $this->render('profile/index.html.twig', [
      'form' => $form,
      'user' => $user,
    ]);
  }
}
