<?php

namespace App\Controller;

use App\Entity\Job;
use App\Entity\Applicant;
use App\Repository\JobRepository;
use App\Repository\ApplicantRepository;
use App\Repository\CompanyRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Uid\Uuid;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;

#[Route(path: '/api/v1')]
#[OA\Tag(name: 'Job')]
class JobController extends AbstractController
{
    use JsonResponseFormat;
    #[Route(path: "/jobs", methods: ["GET"])]
    #[OA\Get(description: "Return all the Jobs with optional filters")]
    #[OA\QueryParameter(name: "title", example: "jobName")]
    #[OA\QueryParameter(name: "company", example: "xyz")]
    #[OA\QueryParameter(name: "location", example: "location")]
    #[OA\QueryParameter(name: "experience", example: "1 year")]
    #[OA\Response(
        response: 200,
        description: "List of Jobs response",
        content: new OA\JsonContent(ref: new Model(type: ResponseDto::class))
    )]

    public function index(
        EntityManagerInterface $entityManager,
        Request $request,
        SerializerInterface $serializer
    ): JsonResponse {
        $title = $request->get('title');
        $company = $request->get('company');
        $location = $request->get('location');
        $experience = $request->get('experience');

        $queryBuilder = $entityManager
            ->getRepository(Job::class)
            ->createQueryBuilder('j')
            ->leftJoin('j.company', 'c', Join::ON);
        if ($title !== null) {
            $queryBuilder->andWhere('j.title LIKE :title')
                ->setParameter(':title', "%$title%");
        }
        if ($company !== null) {
            $queryBuilder->andWhere('c.name LIKE :name')
                ->setParameter(':name', "%$company%");
        }
        if ($location !== null) {
            $queryBuilder->andWhere('c.location LIKE :location')
                ->setParameter(':location', "%$location%");
        }
        if ($experience !== null) {
            $queryBuilder->andWhere('j.experience LIKE :experience')
                ->setParameter(':experience', "%$experience%");
        }

        $queryBuilder->orderBy('j.title', 'ASC');

        $jobs = $queryBuilder->getQuery()->execute();

        $json = $serializer->serialize($jobs, 'json');
        return $this->jsonResponse('List of jobs', $json);
    }

    #[Route(path: "/jobs", methods: ["POST"])]
    #[OA\Post(description: "Create Job of Company")]
    #[OA\RequestBody(
        description: "Json to create the job",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "title", type: "string", example: "JobName"),
                new OA\Property(property: "description", type: "string", example: "JobDescription"),
                new OA\Property(property: "required_skills", type: "string", example: "RequiredSKills"),
                new OA\Property(property: "experience", type: "string", example: "JobExprience"),
                new OA\Property(property: "company_id", type: "string", example: "Provide Company Id"),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Returns the ID of the Job',
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
    public function create(JobRepository $repository, CompanyRepository $companyRepository, ValidatorInterface $validator, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data)) {
            return $this->jsonResponse('No content', ["error" => "No attributes found"], 204);
        }
        $company = $companyRepository->find(Uuid::fromString($data['company_id']));
        if (!$company) {
            return $this->jsonResponse('No record found', ["id" => $data['company_id']], 404);
        }
        $job = new Job();
        $job->setTitle($data['title']);
        $job->setDescription($data['description']);
        $job->setRequiredSkills($data['required_skills']);
        $job->setExperience($data['experience']);
        $job->setCompany($company);
        $violations = $validator->validate($job);

        if (count($violations)) {
            return $this->jsonResponse('Invalid inputs', $this->getViolationsFromList($violations), 400);
        }
        $repository->save($job, true);

        $data = [
            'id' => (string) $job->getId()
        ];

        return $this->jsonResponse('job is created successfully', $data, 201);
    }

    #[Route(path: "/jobs/{id}", methods: ["PUT"])]
    #[OA\Put(description: "Update an Job by its Id")]
    #[OA\RequestBody(
        description: "Json to update the Job",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "title", type: "string", example: "JobName"),
                new OA\Property(property: "descirption", type: "string", example: "JobDescription"),
                new OA\Property(property: "required_skills", type: "string", example: "RequiredSKills"),
                new OA\Property(property: "experience", type: "string", example: "JobExprience"),
                new OA\Property(property: "company_id", type: "string", example: "Provide Company Id"),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the Json of the Job',
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
    public function update(JobRepository $repository, CompanyRepository $companyRepository, SerializerInterface $serializer, ValidatorInterface $validator, Request $request, string $id): Response
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data)) {
            return $this->jsonResponse('No content', ["error" => "No attributes found"], 204);
        }
        $job = $repository->find(Uuid::fromString($id));

        if (!$job) {
            return $this->jsonResponse('No Job record found', ["id" => $id], 404);
        }
        if (isset($data['company_id'])) {
            $company = $companyRepository->find(Uuid::fromString($data['company_id']));
            if (!$company) {
                return $this->jsonResponse('No Company record found', ["id" => $data['company_id']], 404);
            };
        }

        if (isset($data['title'])) {
            $job->setTitle($data['title']);
        }
        if (isset($data['description'])) {
            $job->setDescription($data['description']);
        }
        if (isset($data['required_skills'])) {
            $job->setRequiredSkills($data['required_skills']);
        }
        if (isset($data['experience'])) {
            $job->setExperience($data['experience']);
        }
        if (isset($data['company_id'])) {
            $job->setCompany($company);
        }

        $violations = $validator->validate($job);

        if (count($violations)) {
            return $this->jsonResponse('Invalid inputs', $this->getViolationsFromList($violations), 400);
        }
        $repository->save($job, true);

        return $this->jsonResponse('Updated Succesfully', $serializer->serialize($job, 'json'), 200);
    }

    #[Route(path: "/jobs/{id}", methods: ["GET"])]
    #[OA\Get(description: "Return job by its ID")]
    public function show(JobRepository $repository, string $id): Response
    {
        $job = $repository->find(Uuid::fromString($id));

        if (!$job) {
            return $this->jsonResponse('No Job record found', ["id" => $id], 404);
        }

        $data = [
            'id' => (string) $job->getId(),
            'title' => $job->getTitle(),
            'description' => $job->getDescription(),
            'required_skills' => $job->getRequiredSkills(),
            'experience' => $job->getExperience(),
            'company_id' => (string) $job->getCompany()->getId()
        ];

        return $this->jsonResponse('Success', $data);
    }

    #[Route(path: "/jobs/{id}", methods: ["DELETE"])]
    #[OA\Delete(description: "Delete job by its ID")]
    public function delete(JobRepository $repository, string $id, SerializerInterface $serializer): Response
    {
        $job = $repository->find(Uuid::fromString($id));
        if (!$job) {
            return $this->jsonResponse('No Job record found', ["id" => $id], 404);
        }


        $repository->remove($job, true);

        return $this->jsonResponse('Deleted Scuccesfully', $serializer->serialize($job, 'json'));
    }

    #[Route(path: "/jobs/{id}/applicants", methods: ["GET"])]
    #[OA\Get(description: "Return List of applicants Applied for Job")]
    public function jobApplicants(Job $job): JsonResponse
    {
        $applicants = $job->getApplicants();
        $data = [];

        foreach ($applicants as $applicant) {
            $data[] = [
                'id' => (string)$applicant->getId(),
                'name' => $applicant->getName(),
                'contact' => $applicant->getContact(),
                'job_preferences' => $applicant->getJobPreferences()
            ];
        }

        return $this->jsonResponse('List of applicants Applied for ' . $job->getTitle(), $data, 200);
    }
    #[Route(path: "/job_applicant", methods: ["POST"])]
    #[OA\Post(description: "Submit Job Application of an Applicant")]
    #[OA\RequestBody(
        description: "Json to submit the applicantion",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "job_id", type: "string", example: "JobId"),
                new OA\Property(property: "applicant_id", type: "string", example: "ApplicantId"),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Successfuly Submitted application',
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
    public function addApplicant(JobRepository $repository, ApplicantRepository $applicantRepository, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data)) {
            return $this->jsonResponse('No content', ["error" => "No attributes found"], 204);
        }
        $job = $repository->find(Uuid::fromString($data['job_id']));
        if (!$job) {
            return $this->jsonResponse('No Job record found', ["id" => $data['job_id']], 404);
        }
        $applicant = $applicantRepository->find(Uuid::fromString($data['applicant_id']));
        if (!$applicant) {
            return $this->jsonResponse('No Applicant record Found', ["id" => $data['applicant_id']], 404);
        }
        $existingApplicant = $job->getApplicants()->filter(function ($a) use ($applicant) {
            return $a->getId() === $applicant->getId();
        })->first();
        if ($existingApplicant) {
            return $this->jsonResponse('Applicant already exists', ["applicant_id" => $data['applicant_id'], "job_id" => $data['job_id']], 400);
        }
        $job->addApplicant($applicant);
        $repository->save($job, true);

        return $this->jsonResponse('Successfuly Submitted application', $data, 200);
        ;
    }
    #[Route(path: "/job_applicant", methods: ["GET"])]
    #[OA\Get(description: "Return List of jobs and their applicants")]
    public function jobsApplicants(JobRepository $repository, Request $request): JsonResponse
    {
        $jobs = $repository->findAll();
        $data = [];

        foreach ($jobs as $key => $job) {
            $data[$key] = [
                'id' => $job->getId(),
                'title' => $job->getTitle(),
                'description' => $job->getDescription(),
                'experience' => $job->getExperience()
            ];
            foreach ($job->getApplicants() as $j) {
                $data[$key]['applicants'][] = [
                    'id' => $j->getId(),
                    'name' => $j->getName(),
                    'Contact' => $j->getContact()
                ];
            }
        }

        return $this->jsonResponse('List of jobs and their applicants', $data, 200);
        ;
    }
    #[Route(path: "/job_applicant", methods: ["DELETE"])]
    #[OA\Delete(description: "Delete job application by their IDs")]
    public function DeleteJobsApplicants(JobRepository $repository, SerializerInterface $serializer, ApplicantRepository $applicantRepository, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $job = $repository->find(Uuid::fromString($data['job_id']));
        ;
        $applicant = $applicantRepository->find(Uuid::fromString($data['applicant_id']));

        $job->removeApplicant($applicant);
        $repository->save($job, true);

        return $this->jsonResponse('Deleted Scuccesfully', $serializer->serialize($job, 'json'));
    }
}
