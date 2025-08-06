<?php

namespace TeamTNT\TNTSearch\Engines;

use Exception;
use TeamTNT\TNTSearch\Connectors\FileSystemConnector;
use TeamTNT\TNTSearch\Connectors\MySqlConnector;
use TeamTNT\TNTSearch\Connectors\OracleDBConnector;
use TeamTNT\TNTSearch\Connectors\PostgresConnector;
use TeamTNT\TNTSearch\Connectors\SQLiteConnector;
use TeamTNT\TNTSearch\Connectors\SqlServerConnector;
use TeamTNT\TNTSearch\Stemmer\StemmerInterface;
use TeamTNT\TNTSearch\Support\Collection;
use TeamTNT\TNTSearch\Tokenizer\TokenizerInterface;

trait EngineTrait
{
    /**
     * @return string
     */
    public function getStoragePath(): string
    {
        return $this->config["storage"];
    }

    /**
     * @param array $config
     *
     * @throws Exception
     *
     * @return FileSystemConnector|MySqlConnector|OracleDBConnector|PostgresConnector|SQLiteConnector|SqlServerConnector
     */
    public function createConnector(
        array $config,
    ): FileSystemConnector|MySqlConnector|OracleDBConnector|PostgresConnector|SQLiteConnector|SqlServerConnector {
        if (!isset($config["driver"])) {
            throw new Exception("A driver must be specified.");
        }

        switch ($config["driver"]) {
            case "mysql":
            case "mariadb":
                return new MySqlConnector();
            case "pgsql":
                return new PostgresConnector();
            case "sqlite":
                return new SQLiteConnector();
            case "sqlsrv":
                return new SqlServerConnector();
            case "filesystem":
                return new FileSystemConnector();
            case "oracledb":
                return new OracleDBConnector();
        }

        throw new Exception("Unsupported driver [{$config["driver"]}]");
    }

    /**
     * @param string $query
     *
     * @return void
     */
    public function query(string $query): void
    {
        $this->query = $query;
    }

    /**
     * @param bool $value
     *
     * @return void
     */
    public function disableOutput(bool $value): void
    {
        $this->disableOutput = $value;
    }

    /**
     * @param StemmerInterface $stemmer
     *
     * @return void
     */
    public function setStemmer(StemmerInterface $stemmer): void
    {
        $this->stemmer = $stemmer;
        $this->updateInfoTable("stemmer", get_class($stemmer));
    }

    /**
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey ?? "id";
    }

    /**
     * @param string $text
     *
     * @return array
     */
    public function stemText(string $text): array
    {
        $stemmer = $this->getStemmer();
        $words = $this->breakIntoTokens($text);
        $stems = [];

        foreach ($words as $word) {
            $stems[] = $stemmer->stem($word);
        }

        return $stems;
    }

    /**
     * @return StemmerInterface
     */
    public function getStemmer(): StemmerInterface
    {
        return $this->stemmer;
    }

    /**
     * @param string $text
     *
     * @return array
     */
    public function breakIntoTokens(string $text): array
    {
        if ($this->decodeHTMLEntities) {
            $text = html_entity_decode($text);
        }

        return $this->tokenizer->tokenize($text, $this->stopWords);
    }

    /**
     * @param string $text
     *
     * @return void
     */
    public function info(string $text): void
    {
        if (!$this->disableOutput) {
            echo $text . PHP_EOL;
        }
    }

    /**
     * @param bool $value
     *
     * @return void
     */
    public function setInMemory(bool $value): void
    {
        $this->inMemory = $value;
    }

    /**
     * @param \PDO $index
     *
     * @return void
     */
    public function setIndex(\PDO $index): void
    {
        $this->index = $index;
    }

    /**
     * @param TokenizerInterface $tokenizer
     *
     * @return void
     */
    public function setTokenizer(TokenizerInterface $tokenizer): void
    {
        $this->tokenizer = $tokenizer;
        $this->updateInfoTable("tokenizer", get_class($tokenizer));
    }

    /**
     * @param int $id
     * @param array $document
     *
     * @return void
     */
    public function update(int $id, array $document): void
    {
        $this->delete($id);
        $this->insert($document);
    }

    /**
     * @param array $document
     *
     * @return void
     */
    public function insert(array $document): void
    {
        $this->processDocument(new Collection($document));
        $total = $this->totalDocumentsInCollection() + 1;
        $this->updateInfoTable("total_documents", $total);
    }

    /**
     * @return void
     */
    public function includePrimaryKey(): void
    {
        $this->excludePrimaryKey = false;
    }

    /**
     * @param string $primaryKey
     *
     * @return void
     */
    public function setPrimaryKey(string $primaryKey): void
    {
        $this->primaryKey = $primaryKey;
    }

    /**
     * @param string $word
     *
     * @return int
     */
    public function countWordInWordList(string $word): int
    {
        $res = $this->getWordFromWordList($word);

        if ($res) {
            return $res["num_hits"];
        }

        return 0;
    }

    /**
     * @param bool $value
     *
     * @return void
     */
    public function asYouType(bool $value): void
    {
        $this->asYouType = $value;
    }

    /**
     * @param bool $value
     *
     * @return void
     */
    public function fuzziness(bool $value): void
    {
        $this->fuzziness = $value;
    }

    /**
     * @param string $language
     *
     * @throws Exception
     *
     * @return void
     */
    public function setLanguage(string $language = "no"): void
    {
        $class =
            "TeamTNT\\TNTSearch\\Stemmer\\" .
            ucfirst(strtolower($language)) .
            "Stemmer";

        if (!class_exists($class)) {
            throw new Exception(
                "Language stemmer for [{$language}] does not exist.",
            );
        }

        if (!is_a($class, StemmerInterface::class, true)) {
            throw new Exception(
                "Language stemmer for [{$language}] does not extend Stemmer interface.",
            );
        }

        $this->setStemmer(new $class());
    }

    /**
     * @param \PDO $dbh
     *
     * @return void
     */
    public function setDatabaseHandle(\PDO $dbh)
    {
        $this->dbh = $dbh;
    }
}
