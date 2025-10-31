<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tourze\VisitorManageBundle\DTO\VisitorRegistrationData;
use Tourze\VisitorManageBundle\DTO\VisitorSearchCriteria;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Enum\VisitorStatus;
use Tourze\VisitorManageBundle\Exception\InvalidVisitorDataException;
use Tourze\VisitorManageBundle\Exception\VisitorNotFoundException;
use Tourze\VisitorManageBundle\Repository\VisitorRepository;

readonly class VisitorService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private VisitorRepository $repository,
    ) {
    }

    public function registerVisitor(VisitorRegistrationData $data): Visitor
    {
        $violations = $this->validator->validate($data);

        if ($violations->count() > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = (string) $violation->getMessage();
            }
            throw new InvalidVisitorDataException($errors);
        }

        $visitor = new Visitor();
        if (null !== $data->name) {
            $visitor->setName($data->name);
        }
        if (null !== $data->mobile) {
            $visitor->setMobile($data->mobile);
        }
        if (null !== $data->company) {
            $visitor->setCompany($data->company);
        }
        if (null !== $data->reason) {
            $visitor->setReason($data->reason);
        }
        $visitor->setVehicleNumber($data->vehicleNumber);
        if (null !== $data->appointmentTime) {
            $visitor->setAppointmentTime(\DateTimeImmutable::createFromMutable($data->appointmentTime));
        }
        if (null !== $data->bizUserId) {
            $visitor->setBizUserId($data->bizUserId);
        }
        $visitor->setStatus(VisitorStatus::PENDING);
        $visitor->setCreateTime(new \DateTimeImmutable());
        $visitor->setUpdateTime(new \DateTimeImmutable());

        $this->entityManager->persist($visitor);
        $this->entityManager->flush();

        return $visitor;
    }

    public function getVisitorById(int $id): Visitor
    {
        $visitor = $this->repository->find($id);

        if (null === $visitor) {
            throw new VisitorNotFoundException($id);
        }

        return $visitor;
    }

    /**
     * @return array<int, Visitor>
     */
    public function searchVisitors(VisitorSearchCriteria $criteria): array
    {
        return $this->repository->findByMultipleCriteria(
            $criteria->name,
            $criteria->mobile,
            $criteria->company,
            $criteria->status,
            $criteria->appointmentFrom,
            $criteria->appointmentTo,
            $criteria->page,
            $criteria->limit
        );
    }

    public function approveVisitor(int $id, int $approverId): void
    {
        $visitor = $this->getVisitorById($id);
        $visitor->setStatus(VisitorStatus::APPROVED);
        $visitor->setUpdateTime(new \DateTimeImmutable());

        $this->entityManager->flush();
    }

    public function rejectVisitor(int $id, int $approverId, string $reason): void
    {
        $visitor = $this->getVisitorById($id);
        $visitor->setStatus(VisitorStatus::REJECTED);
        $visitor->setUpdateTime(new \DateTimeImmutable());

        $this->entityManager->flush();
    }

    public function signInVisitor(int $id): void
    {
        $visitor = $this->getVisitorById($id);
        $visitor->setStatus(VisitorStatus::SIGNED_IN);
        $visitor->setSignInTime(new \DateTimeImmutable());
        $visitor->setUpdateTime(new \DateTimeImmutable());

        $this->entityManager->flush();
    }

    public function signOutVisitor(int $id): void
    {
        $visitor = $this->getVisitorById($id);
        $visitor->setStatus(VisitorStatus::SIGNED_OUT);
        $visitor->setSignOutTime(new \DateTimeImmutable());
        $visitor->setUpdateTime(new \DateTimeImmutable());

        $this->entityManager->flush();
    }

    /**
     * @return array<int, Visitor>
     */
    public function getPendingVisitors(): array
    {
        return $this->repository->findPendingApprovals();
    }

    public function countByStatus(VisitorStatus $status): int
    {
        return $this->repository->countByStatus($status);
    }
}
