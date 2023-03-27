<?php

namespace App\Controller;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Uid\Uuid;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route(path: '/api/v1')]
#[OA\Tag(name: 'Company')]
class CompanyController extends AbstractController
{
    use JsonResponseFormat;

    #[Route(path: "/companies", methods: ["GET"])]
    #[OA\Get(description: "Return all the companies.")]
    #[Security(name: "Bearer")]
    public function index(CompanyRepository $repository, SerializerInterface $serializer): Response
    {
        $companies = $repository->findAll();
        if (!$companies) {
            return $this->jsonResponse('No record Found', $companies, 404);
        }

        return $this->jsonResponse('list of comapnies', $serializer->serialize($companies, 'json'), 200);
    }

    #[Route(path: "/companies", methods: ["POST"])]
    #[OA\Post(description: "Create company.")]
    #[OA\RequestBody(
        description: "Json to create the company",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "name", type: "string", example: "CompanyName"),
                new OA\Property(property: "description", type: "string", example: "Lorum upsum txt amit."),
                new OA\Property(property: "location", type: "string", example: "CompanyLocation."),
                new OA\Property(property: "contact", type: "string", example: "112-112-223."),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Returns the ID of the company',
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
    public function create(CompanyRepository $repository, Request $request, ValidatorInterface $validator): Response
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data)) {
            return $this->jsonResponse('No content', ["error" => "No attributes found"], 204);
        }

        $company = new Company();
        $company->setName($data['name']);
        $company->setDescription($data['description']);
        $company->setLocation($data['location']);
        $company->setContact($data['contact']);
        $violations = $validator->validate($company);

        if (count($violations)) {
            return $this->jsonResponse('Invalid inputs', $this->getViolationsFromList($violations), 400);
        }
        $repository->save($company, true);
        $data = ['id' => (string)$company->getId()];
        return $this->jsonResponse('Company is created successfully', $data, 201);
    }

    #[Route(path: "/companies/{id}", methods: ["PUT"])]
    #[OA\Put(description: "Update a company by Id")]
    #[OA\RequestBody(
        description: "Json to update the company",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "name", type: "string", example: "UpdatedCompany"),
                new OA\Property(property: "description", type: "string", example: "Lorem ipsum txt amit."),
                new OA\Property(property: "location", type: "string", example: "UpdatedLocation"),
                new OA\Property(property: "contact", type: "string", example: "112-222-332"),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the Json of the company updated',
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
        description: 'No content found',
        content: new OA\JsonContent(ref: new Model(type: ResponseDto::class))
    )]
    public function update(CompanyRepository $repository, Request $request, SerializerInterface $serializer, string $id, ValidatorInterface $validator): Response
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data)) {
            return $this->jsonResponse('No content found', ["error" => "No attributes to update"], 204);
        }
        $company = $repository->find(Uuid::fromString($id));

        if (!$company) {
            return $this->jsonResponse('No record found', ["id" => $id], 404);
        }

        if (isset($data['name'])) {
            $company->setName($data['name']);
        }
        if (isset($data['description'])) {
            $company->setDescription($data['description']);
        }
        if (isset($data['location'])) {
            $company->setLocation($data['location']);
        }
        if (isset($data['contact'])) {
            $company->setContact($data['contact']);
        }

        $violations = $validator->validate($company);

        if (count($violations)) {
            return $this->jsonResponse('Invalid inputs', $this->getViolationsFromList($violations), 400);
        }
        $repository->save($company);

        return $this->jsonResponse('Updated Succesfully', $serializer->serialize($company, 'json'), 200);
    }

    #[Route(path: "/companies/{id}", methods: ["GET"])]
    #[OA\Get(description: "Return a company by id")]
    #[OA\Response(
        response: 404,
        description: 'No record found',
        content: new OA\JsonContent(ref: new Model(type: ResponseDto::class))
    )]
    public function show(CompanyRepository $repository, string $id): Response
    {
        $company = $repository->find(Uuid::fromString($id));

        if (!$company) {
            return $this->jsonResponse('No record Found', ["id" => $id], 404);
        }

        $data = [
            'id' => (string)$company->getId(),
            'name' => $company->getName(),
            'description' => $company->getDescription(),
            'location' => $company->getLocation(),
            'contact' => $company->getContact(),

        ];
        foreach ($company->getJobs() as $job) {
            $data['jobs'][] = [
                'id' => $job->getId(),
                'title' => $job->getTitle(),
                'description' => $job->getDescription(),
                'experience' => $job->getExperience()
            ];
        }
        return $this->jsonResponse('Success', $data);
    }

    #[Route(path: "/companies/{id}", methods: ["DELETE"])]
    #[OA\Delete(description: "Delete a company by Id")]
    #[OA\Response(
        response: 404,
        description: 'No record found',
        content: new OA\JsonContent(ref: new Model(type: ResponseDto::class))
    )]
    public function delete(CompanyRepository $repository, string $id, SerializerInterface $serializer): Response
    {
        $company = $repository->find(Uuid::fromString($id));

        if (!$company) {
            return $this->jsonResponse('No record Found', ["id" => $id], 404);
        }

        $repository->remove($company, true);
        $data = ['id' => (string)$id];
        return $this->jsonResponse('Deleted Successfully', $data);
    }
}
