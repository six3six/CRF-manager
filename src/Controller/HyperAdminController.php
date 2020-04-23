<?php

namespace App\Controller;

use SensioLabs\AnsiConverter\AnsiToHtmlConverter;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class HyperAdminController extends AbstractController
{
    /**
     * @Route("/hyper_admin", name="hyper_admin")
     * @param Request $request
     * @param KernelInterface $kernel
     * @return Response
     */
    public function index(Request $request, KernelInterface $kernel)
    {
        $param = array();
        if (in_array("command", $request->request->keys())) {
            $application = new Application($kernel);
            $application->setAutoExit(false);
            $input = new StringInput($request->request->get("command"));
            $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);
            $application->run($input, $output);
            $converter = new AnsiToHtmlConverter();
            $content = $output->fetch();
            $param["command_output"] = $converter->convert($content);
        }
        return $this->render('hyper_admin/index.html.twig', $param);
    }
}
