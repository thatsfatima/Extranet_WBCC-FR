<?php
require_once "projetPdf.php";
require_once "../../assets/vendor/convertapi-library-php-master/lib/ConvertApi/ConvertApi.php";
require_once "../../assets/vendor/convertapi-library-php-master/lib/ConvertApi/autoload.php";
use \ConvertApi\ConvertApi;

class Word extends ProjetPdf
{
    public function __construct($fileName, $projet, $immeuble, $sections, $config, $documents)
    {
        parent::__construct($fileName, $projet, $immeuble, $sections, $config, $documents);
    }

    function document()
    {
        parent::document();
        $path = "../../../public/documents/projet/projet_export/";
        $filePath = $path . $this->fileName . '.pdf';
        if (!file_exists($filePath)) {
            throw new Exception("Fichier PDF introuvable : $filePath");
        }
        $destinationPath = $path . $this->fileName . '.docx';
        $this->convertToWord($filePath, $destinationPath);
        echo $this->fileName.'.docx';
    }

    function convertToWord($inputFile, $outputFile)
    {
        // Code snippet is using the ConvertAPI PHP Client: https://github.com/ConvertAPI/convertapi-php
        try {
            $token = array('token_pnu4ogBZ', 'token_UlWxokx3', 'token_TlPkyZzE', 'token_wKgtF7k1');
            ConvertApi::setApiCredentials($token[array_rand($token)]);
            $result = ConvertApi::convert('docx', [
                    'File' => $inputFile,
                ], 'pdf'
            );
            $result->saveFiles($outputFile);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}

?>