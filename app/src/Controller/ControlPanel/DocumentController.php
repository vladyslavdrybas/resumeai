<?php
declare(strict_types=1);

namespace App\Controller\ControlPanel;

use App\Constants\RouteRequirements;
use App\DataTransferObject\ViewResponseDto;
use App\Repository\ResumeRepository;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    "/cp/d",
    name: "cp_document",
    requirements: [
        'resume' => RouteRequirements::UUID->value,
    ]
)]
class DocumentController extends AbstractControlPanelController
{
    #[Route(
        '',
        name: '_list',
        methods: ['GET']
    )]
    public function list(
        ResumeRepository $resumeRepository,
    ): ViewResponseDto {
        $resumes = $resumeRepository->findBy(['owner' => $this->getUser()]);

        return $this->response(
            [
                'resumes' => $resumes,
            ],
            'control-panel/document/list.html.twig'
        );
    }
}