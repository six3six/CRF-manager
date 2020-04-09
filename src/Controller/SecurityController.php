<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('planning');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/profil", name="app_profil")
     */
    public function profil()
    {
        return $this->render("security/profil.html.twig", ["error" => ""]);
    }

    /**
     * @Route("/email", name="app_chgemail", methods={"POST"})
     * @param Request $r
     * @return RedirectResponse
     */
    public function email(Request $r)
    {
        $this->getUser()->setEmail($r->request->get("email"));
        $this->getDoctrine()->getManager()->persist($this->getUser());
        $this->getDoctrine()->getManager()->flush();
        return new RedirectResponse("/profil");
    }

    /**
     * @Route("/password", name="app_chgpassword", methods={"POST"})
     * @param Request $r
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return RedirectResponse
     * @throws InternalErrorException
     */
    public function password(Request $r, UserPasswordEncoderInterface $passwordEncoder)
    {
        $anc = $r->request->get("ancPassword");

        if ($r->request->get("newPassword") !== $r->request->get("confNewPassword")) throw new InternalErrorException("Les mots de passes ne sont pas identiques");

        if (!$passwordEncoder->isPasswordValid($this->getUser(), $anc)) throw new UnauthorizedHttpException("", "Ancien mot de passe invalide");

        $this->getUser()->setPassword($passwordEncoder->encodePassword($this->getUser(), $r->request->get("newPassword")));
        $this->getDoctrine()->getManager()->persist($this->getUser());
        $this->getDoctrine()->getManager()->flush();
        return new RedirectResponse("/profil");
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
