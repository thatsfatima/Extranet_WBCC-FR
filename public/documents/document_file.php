<?php
require_once 'C:/xampp/htdocs/Extranet_WBCC-FR/vendor/autoload.php';

use Aspose\Words\WordsApi;
use Aspose\Words\Model\Requests\CreateDocumentRequest;
use Aspose\Words\Model\Requests\DownloadFileRequest;
use Aspose\Words\Model\Requests\UploadFileRequest;
use Aspose\Words\Model\Requests\SaveAsRequest;
use Aspose\Words\Model\PdfSaveOptionsData;

class Document_file 
{
    protected $wordsApi;
    protected $fileName;

    public function __construct($fileName) {
        $this->wordsApi = new WordsApi('bad09602-c03a-42a8-9edd-1a1fc105593c', '8bb0aa8f0375c767f2fa47cf7f148c0e');
        $this->fileName = $fileName;
    }

    public function createDocument()
    {
        $createDocumentRequest = new CreateDocumentRequest(
            $this->fileName, NULL, NULL
        );
        
        try {
            $this->wordsApi->createDocument($createDocumentRequest);
            echo "Document créé avec succès.";
        } catch (Exception $e) {
            echo "Erreur lors de la création du document : " . $e->getMessage();
        }
    }

    public function downloadDocument($localFilePath)
    {
        $downloadDocumentRequest = new DownloadFileRequest(
            $this->fileName, NULL, NULL
        );
        
        try {
            $response = $this->wordsApi->downloadFile($downloadDocumentRequest);
        
            $tempFilePath = $response->getPathname();
            $tempStream = fopen($tempFilePath, 'r');
        
            file_put_contents($localFilePath, stream_get_contents($tempStream));
            fclose($tempStream);

            $part = explode("documents/", $localFilePath);
            $href = URLROOT."/public/documents/".$part[1];
            echo "Document téléchargé avec succès sur le serveur.";
            echo "<a href='$href' download>Télécharger le document</a>";
        } catch (Exception $e) {
            echo "Erreur lors du téléchargement du document : " . $e->getMessage();
        }
    }

    public function uploadDocument($localFilePath)
    {
        $uploadDocumentRequest = new UploadFileRequest(
            $localFilePath, $this->fileName, NULL
        );
        
        try {
            $response = $this->wordsApi->uploadFile($uploadDocumentRequest, NULL);
            echo "Document uploadé avec succès.";
            return $response;
        } catch (Exception $e) {
            echo "Erreur lors de l'upload du document : " . $e->getMessage();
        }
    }

    public function uploadDocumentFromPdf($localFilePath) {
        // upload file to cloud
        $upload_result = $this->uploadDocument($localFilePath);

        // save as pdf file
        $saveOptions = new PdfSaveOptionsData(array("file_name" => $this->fileName));
        $request = new SaveAsRequest($upload_result->getName(), $saveOptions);
        $result = $this->wordsApi->saveAs($request);

        // download file
        $this->downloadDocument($result->getSavedPath());
    }
}


?>