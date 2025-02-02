<?php 
	class C_ReviewPatroli extends CI_Controller {
		private $table = 'patroli';

		function __construct()
		{
			parent::__construct();
			$this->load->model('ModelsExecuteMaster');
			$this->load->model('GlobalVar');
			$this->load->model('LoginMod');
			$this->load->library('Ciqrcode');

			$this->load->library('pdf_generator');
		}

		public function Read()
		{
			$data = array('success' => false ,'message'=>array(),'data'=>array(),'Penyelesaian'=> 0);

			$TglAwal = $this->input->post('TglAwal');
			$TglAkhir = $this->input->post('TglAkhir');
			$RecordOwnerID = $this->input->post('RecordOwnerID');
			$KodeKaryawan = $this->input->post('KodeKaryawan');
			$LocationID = $this->input->post('LocationID');
			$KodeShift = $this->input->post('KodeShift');
			
			// $SQL = "
			// 	SELECT 
			// 		a.id,
			// 		c.NamaArea				AS NamaLokasi,
			// 		d.NamaSecurity,
			// 		b.NamaCheckPoint,
			// 		a.Koordinat,
			// 		a.Image,
			// 		a.Catatan,
			// 		DATE_FORMAT(a.TanggalPatroli,'%d/%m/%Y %H:%i:%S') Tanggal
			// 	FROM patroli a
			// 	LEFT JOIN tcheckpoint b on a.KodeCheckPoint = b.KodeCheckPoint and a.RecordOwnerID = b.RecordOwnerID AND a.LocationID = b.LocationID
			// 	LEFT JOIN tlokasipatroli c on a.LocationID = c.id AND a.RecordOwnerID = c.RecordOwnerID
			// 	LEFT JOIN tsecurity d on a.KodeKaryawan = d.NIK = a.RecordOwnerID = d.RecordOwnerID
			// 	WHERE DATE(a.TanggalPatroli) BETWEEN '".$TglAwal."' AND '".$TglAkhir."'
			// 	AND a.RecordOwnerID = '".$RecordOwnerID."'
			// ";

			// if ($KodeKaryawan != "") {
			// 	$SQL .= " AND a.KodeKaryawan = '".$KodeKaryawan."' ";
			// }

			// if ($LocationID != "") {
			// 	$SQL .= " AND a.LocationID = '".$LocationID."' ";
			// }

			// $SQL.= " ORDER BY a.TanggalPatroli";


			$SQL = "CALL fn_ReadReview('".$TglAwal."','".$TglAkhir."','".$RecordOwnerID."','".$KodeKaryawan."','".$LocationID."',".$KodeShift.");";

			$rs = $this->db->query($SQL);
			if ($rs) {
				$data['success'] = true;
				$data['data'] = $rs->result();
			}
			echo json_encode($data);
		}

		function png_to_jpg($source, $destination, $quality = 80) {
		    $image = imagecreatefrompng($source);
		    $bg = imagecreatetruecolor(imagesx($image), imagesy($image));

		    // Set background to white
		    $white = imagecolorallocate($bg, 255, 255, 255);
		    imagefilledrectangle($bg, 0, 0, imagesx($image), imagesy($image), $white);

		    imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
		    imagejpeg($bg, $destination, $quality);
		    
		    imagedestroy($image);
		    imagedestroy($bg);
		    
		    return $destination;
		}

		function compress_image($source, $destination, $quality) {
		    $info = getimagesize($source);

		    if ($info['mime'] == 'image/jpeg') {
		        $image = imagecreatefromjpeg($source);
		    } elseif ($info['mime'] == 'image/png') {
		        $image = imagecreatefrompng($source);
		        imagepalettetotruecolor($image); // Convert to true color to enable compression
		        imagealphablending($image, false);
		        imagesavealpha($image, true);
		    } else {
		        return false; // Unsupported format
		    }

		    imagejpeg($image, $destination, $quality);
		    imagedestroy($image);

		    return $destination;
		}

		function compress_remote_image($url, $destination, $quality = 80) {
		    // Download the image
		    $imageData = file_get_contents($url);
		    if ($imageData === false) {
		        die("Failed to download image.");
		    }

		    // Save to a temporary file
		    $tempFile = 'temp_image.jpg';
		    file_put_contents($tempFile, $imageData);

		    // Get image type
		    $info = getimagesize($tempFile);
		    if ($info === false) {
		        die("Invalid image file.");
		    }

		    // Create an image resource
		    if ($info['mime'] == 'image/jpeg') {
		        $image = imagecreatefromjpeg($tempFile);
		    } elseif ($info['mime'] == 'image/png') {
		        $image = imagecreatefrompng($tempFile);
		        imagepalettetotruecolor($image); // Convert to true color
		        imagealphablending($image, false);
		        imagesavealpha($image, true);
		    } else {
		        die("Unsupported image format.");
		    }

		    // Compress and save
		    imagejpeg($image, $destination, $quality);
		    imagedestroy($image);

		    // Remove temp file
		    unlink($tempFile);

		    return $destination;
		}

		public function generatePDF()
		{

			$retdata = array('success' => false ,'message'=>array(),'data'=>array(), 'filename' => '');

			$TglAwal = $this->input->post('TglAwal');
			$TglAkhir = $this->input->post('TglAkhir');
			$RecordOwnerID = $this->input->post('RecordOwnerID');
			$KodeKaryawan = $this->input->post('KodeKaryawan');
			$LocationID = $this->input->post('LocationID');
			$NamaLokasi = $this->input->post('NamaLokasi');
			$KodeShift = $this->input->post('KodeShift');

			// $TglAwal = '2023-12-01';
			// $TglAkhir = '2024-01-01';
			// $RecordOwnerID = 'CL0004';
			// $KodeKaryawan = '';
			// $NamaLokasi = 'BXSEA';
			// $LocationID = '';

			// Set up your PDF content
			$DateCreateion = date("Ymdhis");
			try {
				$baseDir = FCPATH.'Assets/doc/'.$RecordOwnerID.$DateCreateion.'.pdf';
		        $this->pdf_generator->AddPage();
		        $this->pdf_generator->SetTitle("Laporan Patroli" ,true);
		        $this->pdf_generator->SetFont('Arial','B',16);
		        $this->pdf_generator->Cell(0,5,'Laporan Patroli',0,1,'C');

		        $this->pdf_generator->SetFont('Arial','',12);
		        if($TglAwal == $TglAkhir){
		        	$this->pdf_generator->Cell(0,5,'Tanggal : ' . $TglAwal,0,1,'C');
		        }
		        else{
		        	$this->pdf_generator->Cell(0,5,'Periode : ' . $TglAwal . ' s/d '. $TglAkhir,0,1,'C');	
		        }

		        $this->pdf_generator->Cell(0,5,'Lokasi Project : ' . $NamaLokasi,0,1,'C');

		        $pdfWidth = $this->pdf_generator->GetPageWidth();
		        $lineY = 30;
		        $centerX = $pdfWidth / 2;

		        $lineLength = 180;

		        $startX = $centerX - ($lineLength / 2);
				$endX = $centerX + ($lineLength / 2);

				// Draw a line from (x1, y1) to (x2, y2)
				$this->pdf_generator->Line($startX, $lineY, $endX, $lineY);


				$SQL = "CALL fn_ReadReview('".$TglAwal."','".$TglAkhir."','".$RecordOwnerID."','".$KodeKaryawan."','".$LocationID."',".$KodeShift.");";

				$rs = $this->db->query($SQL);

				if ($rs) {
					$data = $rs->result();

					$xBox = 20;
					$yBox = 35;

					$wBox = $pdfWidth / 4;

					$index = 0;
					$lastBR = 2;

					$maxContentHeight = 270;

					$contentHeight = 0;
					foreach ($data as $key) {
						// echo "X: " . $xBox. " - Y: ".$yBox.' width : '.$wBox.' Ydaa : '.$this->pdf_generator->GetY()."<br>";
						$contentHeight = max($contentHeight, $this->pdf_generator->GetY());
						// echo $contentHeight." - ".$maxContentHeight."<br>";
						// var_dump($key);
						if ($contentHeight > $maxContentHeight) {
							$this->pdf_generator->AddPage();
					        $this->pdf_generator->SetTitle("Laporan Patroli" ,true);
					        $this->pdf_generator->SetFont('Arial','B',16);
					        $this->pdf_generator->Cell(0,5,'Laporan Patroli',0,1,'C');

					        $this->pdf_generator->SetFont('Arial','',12);
					        if($TglAwal == $TglAkhir){
					        	$this->pdf_generator->Cell(0,5,'Tanggal : ' . $TglAwal,0,1,'C');
					        }
					        else{
					        	$this->pdf_generator->Cell(0,5,'Periode : ' . $TglAwal . ' s/d '. $TglAkhir,0,1,'C');	
					        }

					        $this->pdf_generator->Cell(0,5,'Lokasi Project : ' . $NamaLokasi,0,1,'C');

					        $pdfWidth = $this->pdf_generator->GetPageWidth();
					        $lineY = 30;
					        $centerX = $pdfWidth / 2;

					        $lineLength = 180;

					        $startX = $centerX - ($lineLength / 2);
							$endX = $centerX + ($lineLength / 2);

							// Draw a line from (x1, y1) to (x2, y2)
							$this->pdf_generator->Line($startX, $lineY, $endX, $lineY);

							$xBox = 20;
							$yBox = 35;

							$wBox = $pdfWidth / 4;

							$index = 0;
							$lastBR = 2;
							$contentHeight = 0;
						}
						$this->pdf_generator->Rect($xBox, $yBox, $wBox, 75);

						// $this->pdf_generator->SetXY($xBox + 6,$yBox + 45);
						// $this->pdf_generator->MultiCell($wBox / 2, 2, $key->NamaLokasi);

						// $imagePath = base_url().'Assets/images/patroli/'.$key->Image;
						$imagePath = FCPATH.'Assets/images/patroli/'.$key->Image;
						$compressed_image = FCPATH.'Assets/images/converted/'.$key->Image;
						// $jpeg_image = $this->png_to_jpg($imagePath, base_url().'Assets/images/converted/'.$key->Image, 50);
						$this->compress_image($imagePath, $compressed_image, 50);
						$this->pdf_generator->Image($compressed_image, $xBox + 5, $yBox + 3, $wBox - 10, 40);
						// $this->pdf_generator->Ln(5);
						// $this->pdf_generator->Cell($wBox /2, 2, $key->NamaLokasi);

						// $textX = $xBox;
						// $textY = $yBox;
						// $this->pdf_generator->SetXY($xBox + 5,$yBox);
						// $this->pdf_generator->Cell($wBox - 5, 2, $key->NamaLokasi);
						// $this->pdf_generator->Rect($xBox +5, $yBox + 45, $wBox - 10, 2);
						$this->pdf_generator->SetXY($xBox + 6,$yBox + 45);
						$this->pdf_generator->SetFont('Arial','B',12);
						$this->pdf_generator->MultiCell($wBox - 12, 2, $key->NamaLokasi,0,'C');
						$this->pdf_generator->SetXY($xBox + 6,$yBox + 48);
						$this->pdf_generator->SetFont('Arial','',10);
						$this->pdf_generator->MultiCell($wBox - 12, 4, $key->NamaCheckPoint,0,'C');
						$this->pdf_generator->SetXY($xBox + 3,$yBox + 57);
						$this->pdf_generator->MultiCell($wBox - 12, 4, "Security:".$key->NamaSecurity,0);
						$this->pdf_generator->SetXY($xBox + 3,$yBox + 66);
						$this->pdf_generator->MultiCell($wBox - 12, 2, "Tanggal:".$key->TanggalPatroli,0);
						$this->pdf_generator->SetXY($xBox + 3,$yBox + 70);
						$this->pdf_generator->MultiCell($wBox - 12, 2, "Jam:".$key->JamAktual,0);
						// $this->pdf_generator->Cell($wBox - 10, 2, $key->NamaLokasi);
							// $this->pdf_generator->SetXY($xBox, $yBox);
						

						if ($index == $lastBR) {
							$this->pdf_generator->Ln(5);
							$lastBR += 3;
							$yBox += 80;
							$xBox = 20;
						}
						else{
							$xBox += $wBox + 5;
							// $yBox = 35;
							// $this->pdf_generator->SetXY($xBox, $yBox);
							// $this->pdf_generator->Cell($wBox - 5, 2, $key->NamaLokasi); 
						}
						$index += 1;
					}
				}
		        // Output the PDF
		        $this->pdf_generator->Output('F',$baseDir);	

		        $retdata['success'] = true;
		        $retdata['filename'] = $RecordOwnerID.$DateCreateion;
			} catch (Exception $e) {
				$retdata['success'] = false;
				$retdata['message'] = $e->getMessage();
			}

			echo json_encode($retdata);
		}
	}
?>