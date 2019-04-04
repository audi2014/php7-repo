# php7-repo
```PHP
<?php
/**
 * Created by PhpStorm.
 * User: andriyprosekov
 * Date: 27/07/2018
 * Time: 11:10
 */

namespace Audi2014\Auth\Credential;

use Audi2014\Repo\AbstractRepo;

class CredentialFbRepo extends AbstractRepo {

    public function getTable(): string {
        return '__auth__credential_fb';
    }

    public function getFields(): array {
        return [
            '__auth__credential_fb.*',
        ];
    }

    public function getGroupBy(): ?string {
        return '__auth__credential_fb.profileId';
    }

    /**
     * @param $key
     * @param $value
     * @return CredentialFbEntity
     */
    public function fetchFirstByKeyValue(string $key, $value): ?CredentialFbEntity {
        return parent::fetchFirstByKeyValue($key, $value); // TODO: Change the autogenerated stub
    }

    /**
     * @param CredentialFbEntity $data
     * @return void
     * @throws \Exception
     */
    public function insertCredential(CredentialFbEntity &$data): void {
        $data->createdAt = time();
        $data->updatedAt = time();
        $data->id = parent::insertRow($data);
    }


    function willInsertData(array $data): array {
        $data['updatedAt'] = time();
        $data['createdAt'] = time();
        return $data;
    }

    function willUpdateData(array $data): array {
        $data['updatedAt'] = time();
        return $data;
    }


    protected function getEntityClass(): string {
        return CredentialFbEntity::class;
    }
}
```
