<?php

namespace App\Controller;

use App\Entity\Applicant;
use App\Repository\ApplicantRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;

#[Route(path: '/api/v1')]
#[OA\Tag(name: 'Applicant')]
class ApplicantController extends AbstractController
{
    use JsonResponseFormat;

    #[Route(path: "/applicants", methods: ["GET"])]
    #[OA\Get(description: "Return all the applicants.")]
    #[Security(name: "Bearer")]
    public function index(ApplicantRepository $repository): JsonResponse
    {
        $applicants = $repository->findAll();
        $data = [];

        foreach ($applicants as $applicant) {
            $data[] = [
                'id' => (string)$applicant->getId(),
                'name' => $applicant->getName(),
                'contact' => $applicant->getContact(),
                'job_preferences' => $applicant->getJobPreferences()
            ];
        }

        return $this->jsonResponse('List of Applicants', $data, 200);
    }

    #[Route(path: "/applicants", methods: ["POST"])]
    #[OA\Post(description: "Create Applicant")]
    #[OA\RequestBody(
        description: "Json to create the applicant",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "name", type: "string", example: "ApplicantName"),
                new OA\Property(property: "contact", type: "string", example: "111-222-333"),
                new OA\Property(property: "job_preferences", type: "string", example: "ApplicantJobPreferences"),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Returns the ID of the applicant',
        content: new OA\JsonContent(ref: new Model(type: ResponseDto::class))
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid inputs',
        content: new OA\JsonContent(ref: new Model(type: ResponseDto::class))
    )]
    #[OA\Response(
        response: 204,
        description: 'No content',
        content: new OA\JsonContent(ref: new Model(type: ResponseDto::class))
    )]
    public function create(ApplicantRepository $repository, Request $request, ValidatorInterface $validator): Response
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data)) {
            return $this->jsonResponse('No content', ["error" => "No attributes found"], 204);
        }
        $applicant = new Applicant();
        $applicant->setName($data['name']);
        $applicant->setContact($data['contact']);
        $applicant->setJobPreferences($data['job_preferences']);
        $violations = $validator->validate($applicant);

        if (count($violations)) {
            return $this->jsonResponse('Invalid inputs', $this->getViolationsFromList($violations), 400);
        }
        $repository->save($applicant, true);
        $data = ['id' => (string)$applicant->getId()];
        return $this->jsonResponse('Applicant is created successfully', $data, 201);
    }

    #[Route(path: "/applicants/{id}", methods: ["GET"])]
    #[OA\Get(description: "Return an applicant by its ID")]
    public function show(ApplicantRepository $repository, string $id): JsonResponse
    {
        $applicant = $repository->find(Uuid::fromString($id));

        if (!$applicant) {
            return $this->jsonResponse('No record Found', ["id" => $id], 404);
        }

        $data = [
            'id' => (string)$applicant->getId(),
            'name' => $applicant->getName(),
            'contact' => $applicant->getContact(),
            'job_preferences' => $applicant->getJobPreferences()
        ];

        return $this->jsonResponse('Success', $data);
    }

    #[Route(path: "/applicants/{id}", methods: ["PUT"])]
    #[OA\Put(description: "Update an applicant by its Id")]
    #[OA\RequestBody(
        description: "Json to update the applicant",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "name", type: "string", example: "ApplicantName"),
                new OA\Property(property: "contact", type: "string", example: "ApplicantContact"),
                new OA\Property(property: "jobPreferences", type: "string", example: "ApplicantJobPreferences"),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the Json of the applicant',
        content: new OA\JsonContent(ref: new Model(type: ResponseDto::class))
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid inputs',
        content: new OA\JsonContent(ref: new Model(type: ResponseDto::class))
    )]
    #[OA\Response(
        response: 404,
        description: 'No record found',
        content: new OA\JsonContent(ref: new Model(type: ResponseDto::class))
    )]
    #[OA\Response(
        response: 204,
        description: 'No content',
        content: new OA\JsonContent(ref: new Model(type: ResponseDto::class))
    )]
    public function update(ApplicantRepository $repository, Request $request, SerializerInterface $serializer, string $id, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data)) {
            return $this->jsonResponse('No content', ["error" => "No attributes found"], 204);
        }
        $applicant = $repository->find(Uuid::fromString($id));

        if (!$applicant) {
            return $this->jsonResponse('No record found', ["id" => $id], 404);
        }
        if (isset($data['name'])) {
            $applicant->setName($data['name']);
        }
        if (isset($data['contact'])) {
            $applicant->setContact($data['contact']);
        }
        if (isset($data['job_preferences'])) {
            $applicant->setJobPreferences($data['job_preferences']);
        }
        $violations = $validator->validate($applicant);

        if (count($violations)) {
            return $this->jsonResponse('Invalid inputs', $this->getViolationsFromList($violations), 400);
        }
        $repository->save($applicant, true);

        return $this->jsonResponse('Updated Succesfully', $serializer->serialize($applicant, 'json'), 200);
    }

    #[Route(path: "/applicants/{id}", methods: ["DELETE"])]
    #[OA\Delete(description: "Delete an applicant by its Id")]
    public function delete(ApplicantRepository $repository, string $id, SerializerInterface $serializer): JsonResponse
    {
        $applicant = $repository->find(Uuid::fromString($id));

        if (!$applicant) {
            return $this->jsonResponse('No record found', ["id" => $id], 404);
        }

        $repository->remove($applicant, true);

        return $this->jsonResponse('Deleted Scuccesfully', $serializer->serialize($applicant, 'json'));
    }

    #[Route(path: "/applicants/{id}/jobs", methods: ["GET"])]
    #[OA\Get(description: "Return all jobs applied by applicant")]
    public function jobApplicants(Applicant $applicant): JsonResponse
    {
        $jobs = $applicant->getJobsApplied();
        $data = [];

        foreach ($jobs as $job) {
            $data[] = [
                'id' => (string)$job->getId(),
                'title' => $job->getTitle(),
                'description' => $job->getDescription(),
                'required_skills' => $job->getRequiredSkills(),
                'experience' => $job->getExperience()
            ];
        }

        return $this->jsonResponse('List of Jobs Applied by ' . $applicant->getName(), $data, 200);
    }
}
