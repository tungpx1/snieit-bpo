<?php


namespace App\services;


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class HandoverPaperService
{
    public function createHandoverPaper($deliver, $receiver, $assets, $nameOfDoc, $date, $numberOfReport, $note = 'Cấp mới') {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        $phpWord->addTitleStyle(null, array('size' => 14, 'bold' => true, 'allCaps' => true));
        $phpWord->addTitleStyle(1, array('size' => 11, 'bold' => true));

        $headerStyle = array('bold' => true, 'size' => 11);
        $rowStyle = array('size' => 11);
        $alignTextCell = ['align' => 'center'];

        $this->_generateHeaderHandoverPaper($section, $numberOfReport);
        $section->addTextBreak(4);
        $this->_generateOverall($section, $phpWord, $deliver, $receiver);
        $section->addTextBreak();
        $now = new \DateTime($date);
        $section->addTitle("II. Delivery date: {$now->format('M/d/Y')}", 1);
        $section->addTextBreak();
        $this->_generateMainHandoverPaper($section, $phpWord, $headerStyle, $alignTextCell, $rowStyle, $assets);
        $section->addTextBreak();
        $this->_generateEndingHandoverPaper($section, $alignTextCell, $note);

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $nameOfFile = "{$nameOfDoc}.docx";
        $objWriter->save($nameOfFile);

        return $this->uploadFileToGoogleDrive($nameOfFile);
    }

    /**
     * @param Section $section
     */
    private function _generateHeaderHandoverPaper(Section $section, $numberOfReport): void
    {
        $section->addTitle(env('COMPANY_NAME', "CÔNG TY TNHH SGS VIỆT NAM"), 0);
        $section->addText(env('COMPANY_ADDRESS', "Tầng 2, 3, 4, 7 số 314 Minh Khai, Phường Minh Khai, Quận Hai Bà Trưng, Hà Nội"), array('size' => 11, 'italic' => true));
        $section->addTextBreak(4);
        $section->addText('MINUTES OF HANDOVER OF ASSETS', array('size' => 12, 'bold' => true, 'allCaps' => true), ['align' => 'center']);
        $section->addText("No: SGS-{$numberOfReport}", ['size' => 12], ['align' => 'center']);
    }

    /**
     * @param Section $section
     * @param PhpWord $phpWord
     */
    private function _generateOverall(Section $section, PhpWord $phpWord, $deliver, $receiver)
    {
        $section->addTitle('I. General information::', 1);
        $fancyTableStyleName = 'Fancy Table';
        $fancyTableStyle = array(
            'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER
        );
        $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, []);

        $table = $section->addTable($fancyTableStyleName);
        $table->addRow();
        $cell_1 = $table->addCell(4500);
        $cell_1->addText("Handed over by: {$deliver->first_name}");
        $cell_1->addText('Project:');
        $cell_1->addText("Department: {$deliver->jobtitle}");

        $cell_2 = $table->addCell(4500);
        $cell_2->addText("Taken over by: {$receiver->first_name}");
        $cell_2->addText('Project:');
        $cell_2->addText("Department: {$receiver->jobtitle}");
    }

    /**
     * @param Section $section
     * @param PhpWord $phpWord
     * @param array $headerStyle
     * @param array $alignTextCell
     * @param array $rowStyle
     * @param $assets
     */
    private function _generateMainHandoverPaper(Section $section, PhpWord $phpWord, array $headerStyle, array $alignTextCell, array $rowStyle, $assets): void
    {
        $section->addTitle('III. Delivery contents', 1);

        $checkOutTable = 'CHECK_OUT_TABLE';
        $checkOutTableStyle = array(
            'borderSize' => 6,
            'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
        );

        $fancyTableCellStyle = array('valign' => 'center');

        $phpWord->addTableStyle($checkOutTable, $checkOutTableStyle, []);

        $table = $section->addTable($checkOutTable);
        $table->addRow();

        $table->addCell(1000, $fancyTableCellStyle)->addText('No', $headerStyle, $alignTextCell);
        $table->addCell(2200, $fancyTableCellStyle)->addText('Device asset name', $headerStyle, $alignTextCell);
        $table->addCell(1300, $fancyTableCellStyle)->addText('Quantity', $headerStyle, $alignTextCell);
        $table->addCell(2000, $fancyTableCellStyle)->addText('Status', $headerStyle, $alignTextCell);
        $table->addCell(2500, $fancyTableCellStyle)->addText('Serial number', $headerStyle, $alignTextCell);

        $usedAssetCounter = 2;

        $count = 1;
        foreach ($assets as $key => $asset) {
            $table->addRow();
            $table->addCell(1000, $fancyTableCellStyle)->addText($count, $rowStyle, $alignTextCell);
            $table->addCell(2200, $fancyTableCellStyle)->addText($asset->asset_tag." ({$asset->model->name})", $rowStyle);
            $table->addCell(1300, $fancyTableCellStyle)->addText(1, $rowStyle, $alignTextCell);
            $table->addCell(2000, $fancyTableCellStyle)->addText($asset->checkout_counter < $usedAssetCounter ? 'New' : 'Used', $rowStyle);
            $table->addCell(2500, $fancyTableCellStyle)->addText($asset->serial, $rowStyle);
            $count++;
            if (isset($asset->sub_assets) and count($asset->sub_assets) > 0) {
                foreach ($asset->sub_assets as $k => $sub) {
                    $count++;
                    $table->addRow();
                    $table->addCell(1000, $fancyTableCellStyle)->addText($count, $rowStyle, $alignTextCell);
                    $table->addCell(2200, $fancyTableCellStyle)->addText($sub->asset_tag." ({$sub->model->name})", $rowStyle);
                    $table->addCell(1300, $fancyTableCellStyle)->addText(1, $rowStyle, $alignTextCell);
                    $table->addCell(2000, $fancyTableCellStyle)->addText($sub->checkout_counter < $usedAssetCounter ? 'New' : 'Used', $rowStyle);
                    $table->addCell(2500, $fancyTableCellStyle)->addText($sub->serial, $rowStyle);
                }
            }
        }
    }

    /**
     * @param Section $section
     * @param array $alignTextCell
     * @param $note
     */
    private function _generateEndingHandoverPaper(Section $section, array $alignTextCell, string $note): void
    {
        $textPurposeRun = $section->addTextRun('purposeParagraph');
        $textPurposeRun->addText('Item Purpose:', ['bold' => true], []);
        $textPurposeRun->addText(' Property used for working purpose.');
        $section->addTextBreak(1);

        $textResponsibilityRun = $section->addTextRun('responsibilityParagraph');
        $textResponsibilityRun->addText('Responsibility:', ['bold' => true], []);
        $textResponsibilityRun->addText(env('RESPONSIBLE_1', " Bên nhận tài sản có trách nhiệm bảo quản, giữ gìn tài sản thiết bị trên kể từ ngày nhận bàn giao. Nếu bị hỏng hoặc mất mát do lỗi chủ quan, người nhận tài sản phải chịu trách nhiệm chi phí sửa chữa, đền bù hoàn trả cho công ty."));
        $section->addTextBreak(1);
        $section->addText(env('RESPONSIBLE_2', "Biên bản này được làm thành 02 bản tiếng Việt có giá trị pháp lý như nhau, mỗi bên giữ một bản."));
        $section->addTextBreak(1);
        $section->addText("Separate Notes : $note");

        $section->addTextBreak(2);

        $tableSignature = $section->addTable('signatureTable');
        $tableSignature->addRow();
        $cell_1 = $tableSignature->addCell(4500);
        $cell_1->addText("HANDOVER PERSON", ['allCaps' => true, 'size' => 11, 'bold' => true], $alignTextCell);

        $cell_2 = $tableSignature->addCell(4500);
        $cell_2->addText("HANDOVER RECIPIENT", ['allCaps' => true, 'size' => 11, 'bold' => true], $alignTextCell);
    }

    // application/vnd.google-apps.spreadsheet
    private function uploadFileToGoogleDrive($fileName, $mimeType = 'application/vnd.google-apps.document') {
        $client = new \Google_Client();
        $client->setAuthConfig(storage_path('snipeit-286308-cd62d19e95c5.json'));
        $client->useApplicationDefaultCredentials();
        $client->addScope(\Google_Service_Drive::DRIVE);
        $driveService = new \Google_Service_Drive($client);

        $fileMetadata = new \Google_Service_Drive_DriveFile(
            array(
                'name' => $fileName,
                'mimeType' => $mimeType
            )
        );
        $fileMetadata->setParents([env('FOLDER_HANDOVER_PAPER')]);
        $content = file_get_contents(public_path($fileName));
        $file = $driveService->files->create($fileMetadata, array(
            'data' => $content,
            'mimeType' => 'text/docs',
            'uploadType' => 'multipart',
            'fields' => 'id'));
        $driveService->getClient()->setUseBatch(true);

        $batch = $driveService->createBatch();
        $userPermission = new \Google_Service_Drive_Permission([
            'type' => 'anyone', // user | group | domain | anyone
            'role' => 'reader', // organizer | owner | writer | commenter | reader
        ]);
        $request = $driveService->permissions->create($file->id, $userPermission, ['fields' => 'id']);
        $batch->add($request, 'user');
        $batch->execute();

        return $file->id;
    }

    public function createSpreadSheet($assets, $name='asset_list.xlsx') {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Asset Tag')
            ->setCellValue('B1', 'Serial')
            ->setCellValue('C1', 'Model')
            ->setCellValue('D1', 'Category');

        $i = 2;
        foreach($assets as $key => $asset) {
            $spreadsheet->getActiveSheet()
                ->setCellValue("A$i", $asset->asset_tag)
                ->setCellValue("B$i", $asset->serial)
                ->setCellValue("C$i", $asset->model->name)
                ->setCellValue("D$i", $asset->model->category->name);
            $i++;
        }

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx"); //Xls is also possible
        $writer->save($name);

        $fileId = $this->uploadFileToGoogleDrive($name);
        return "https://docs.google.com/file/d/".$fileId."/edit";
    }
}
