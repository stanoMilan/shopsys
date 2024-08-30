<?php

declare(strict_types=1);

namespace App\Model\Product\Flag;

use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade as BaseFlagFacade;


/**
 * @property \App\Model\Product\Flag\FlagRepository $flagRepository
 * @method \App\Model\Product\Flag\Flag getById(int $flagId)
 * @method \App\Model\Product\Flag\Flag[] getAll()
 * @method dispatchFlagEvent(\App\Model\Product\Flag\Flag $flag, string $eventType)
 * @method \App\Model\Product\Flag\Flag[] getByIds(int[] $flagIds)
 * @method \App\Model\Product\Flag\Flag getByUuid(string $uuid)
 * @method \App\Model\Product\Flag\Flag[] getByUuids(string[] $uuids)
 * @method \App\Model\Product\Flag\Flag[] getVisibleFlagsByIds(int[] $flagsIds, string $locale)
 */
class FlagFacade extends BaseFlagFacade
{
    /**
     * @param string $akeneoCode
     * @return \App\Model\Product\Flag\Flag|null
     */
    public function findByAkeneoCode(string $akeneoCode): ?Flag
    {
        return $this->flagRepository->findByAkeneoCode($akeneoCode);
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @return array
     */
    public function getAllFlagAkeneoCodes(): array
    {
        return $this->flagRepository->getAllFlagAkeneoCodes();
    }

    /**
     * @param string $akeneoCode
     * @throws \RuntimeException
     * @return bool
     */
    public function deleteByAkeneoCode(string $akeneoCode): bool
    {
        $flag = $this->flagRepository->findByAkeneoCode($akeneoCode);

        if ($flag !== null) {
            $this->em->remove($flag);
            $this->em->flush();

            return true;
        }

        return false;
    }

    /**
     * @param string $locale
     * @return \App\Model\Product\Flag\Flag[]
     */
    public function getAllVisibleFlags(string $locale): array
    {
        return $this->flagRepository->getAllVisibleFlags($locale);
    }

    /**
     * @param string $uuid
     * @param string $locale
     * @return \App\Model\Product\Flag\Flag
     */
    public function getVisibleByUuid(string $uuid, string $locale): Flag
    {
        return $this->flagRepository->getVisibleByUuid($uuid, $locale);
    }

    /**
     * @param int $flagId
     * @param string $locale
     * @return \App\Model\Product\Flag\Flag
     */
    public function getVisibleFlagById(int $flagId, string $locale): Flag
    {
        return $this->flagRepository->getVisibleFlagById($flagId, $locale);
    }

    /**
     * @param int $flagId
     * @return \App\Model\Product\Flag\FlagDependenciesData
     */
    public function getFlagDependencies(int $flagId): FlagDependenciesData
    {
        return $this->flagRepository->getFlagDependencies($flagId);
    }
}
