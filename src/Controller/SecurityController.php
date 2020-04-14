<?php

namespace App\Controller;

use App\Form\UserInfoType;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

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
     * @Route("/profil", name="user_profil")
     * @param Request $request
     * @return Response
     */
    public function user_edit(Request $request)
    {
        $user = $this->getUser();
        $form = $this->createForm(UserInfoType::class, $user);
        $form->remove("skills");
        $form->remove("display_name");

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('admin_user_view');
        }

        return $this->render('admin/user/edit.html.twig', [
            'form' => $form->createView(),
            'delete' => true,
            "user" => $user
        ]);
    }

    /**
     * @Route("/chginfos", name="app_chginfo", methods={"POST"})
     * @param Request $r
     * @return RedirectResponse
     */
    public function email(Request $r)
    {

        $this->getUser()->setEmail($r->request->get("email"));
        $this->getUser()->setCellphone($r->request->get("cellphone"));
        $this->getUser()->setPhone($r->request->get("phone"));
        $this->getUser()->setAddress($r->request->get("address"));
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
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
