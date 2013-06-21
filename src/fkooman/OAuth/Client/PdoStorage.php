<?php

/**
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace fkooman\OAuth\Client;

class PdoStorage
{
    private $_pdo;

    public function __construct(\PDO $p)
    {
        $this->_pdo = $p;
    }

    public function getAccessToken($callbackId, $userId, $scope)
    {
        $stmt = $this->_pdo->prepare("SELECT * FROM oauth_access_tokens WHERE callback_id = :callback_id AND user_id = :user_id AND scope = :scope");
        $stmt->bindValue(":callback_id", $callbackId, PDO::PARAM_STR);
        $stmt->bindValue(":user_id", $userId, PDO::PARAM_STR);
        $stmt->bindValue(":scope", $scope, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function storeAccessToken($callbackId, $userId, $scope, $accessToken, $issueTime, $expiresIn)
    {
        $stmt = $this->_pdo->prepare("INSERT INTO oauth_access_tokens (callback_id, user_id, scope, access_token, issue_time, expires_in) VALUES(:callback_id, :user_id, :scope, :access_token, :issue_time, :expires_in)");
        $stmt->bindValue(":callback_id", $callbackId, PDO::PARAM_STR);
        $stmt->bindValue(":user_id", $userId, PDO::PARAM_STR);
        $stmt->bindValue(":scope", $scope, PDO::PARAM_STR);
        $stmt->bindValue(":access_token", $accessToken, PDO::PARAM_STR);
        $stmt->bindValue(":issue_time", $issueTime, PDO::PARAM_INT);
        $stmt->bindValue(":expires_in", $expiresIn, PDO::PARAM_INT);
        $stmt->execute();

        return 1 === $stmt->rowCount();
    }

    public function deleteAccessToken($callbackId, $userId, $accessToken)
    {
        $stmt = $this->_pdo->prepare("DELETE FROM oauth_access_tokens WHERE callback_id = :callback_id AND user_id = :user_id AND access_token = :access_token");
        $stmt->bindValue(":callback_id", $callbackId, PDO::PARAM_STR);
        $stmt->bindValue(":user_id", $userId, PDO::PARAM_STR);
        $stmt->bindValue(":access_token", $accessToken, PDO::PARAM_STR);
        $stmt->execute();

        return 1 === $stmt->rowCount();
    }

    public function getState($callbackId, $state)
    {
        $stmt = $this->_pdo->prepare("SELECT * FROM oauth_states WHERE callback_id = :callback_id AND state = :state");
        $stmt->bindValue(":callback_id", $callbackId, PDO::PARAM_STR);
        $stmt->bindValue(":state", $state, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function storeState($callbackId, $userId, $scope, $returnUri, $state)
    {
        $stmt = $this->_pdo->prepare("INSERT INTO oauth_states (callback_id, user_id, scope, return_uri, state) VALUES(:callback_id, :user_id, :scope, :return_uri, :state)");
        $stmt->bindValue(":callback_id", $callbackId, PDO::PARAM_STR);
        $stmt->bindValue(":user_id", $userId, PDO::PARAM_STR);
        $stmt->bindValue(":scope", $scope, PDO::PARAM_STR);
        $stmt->bindValue(":return_uri", $returnUri, PDO::PARAM_STR);
        $stmt->bindValue(":state", $state, PDO::PARAM_STR);
        $stmt->execute();

        return 1 === $stmt->rowCount();
    }

    public function deleteStateIfExists($callbackId, $userId)
    {
        $stmt = $this->_pdo->prepare("DELETE FROM oauth_states WHERE callback_id = :callback_id AND user_id = :user_id");
        $stmt->bindValue(":callback_id", $callbackId, PDO::PARAM_STR);
        $stmt->bindValue(":user_id", $userId, PDO::PARAM_STR);
        $stmt->execute();

        return 1 === $stmt->rowCount();
    }

    // FIXME: should this not include the userId?
    public function deleteState($callbackId, $state)
    {
        $stmt = $this->_pdo->prepare("DELETE FROM oauth_states WHERE callback_id = :callback_id AND state = :state");
        $stmt->bindValue(":callback_id", $callbackId, PDO::PARAM_STR);
        $stmt->bindValue(":state", $state, PDO::PARAM_STR);
        $stmt->execute();

        return 1 === $stmt->rowCount();
    }

    public function getChangeInfo()
    {
        $stmt = $this->_pdo->prepare("SELECT MAX(patch_number) AS patch_number, description FROM db_changelog WHERE patch_number IS NOT NULL");
        $stmt->execute();
        // ugly hack because query will always return a result, even if there is none...
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return NULL === $result['patch_number'] ? FALSE : $result;
    }

    public function addChangeInfo($patchNumber, $description)
    {
        $stmt = $this->_pdo->prepare("INSERT INTO db_changelog (patch_number, description) VALUES(:patch_number, :description)");
        $stmt->bindValue(":patch_number", $patchNumber, PDO::PARAM_INT);
        $stmt->bindValue(":description", $description, PDO::PARAM_STR);
        $stmt->execute();

        return 1 === $stmt->rowCount();
    }

    public function dbQuery($query)
    {
        $this->_pdo->exec($query);
    }

}
