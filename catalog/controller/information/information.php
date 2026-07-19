<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerInformationInformation extends Controller {
	public function index() {
		$this->load->language('information/information');

		$this->load->model('catalog/information');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		if (isset($this->request->get['information_id'])) {
			$information_id = (int)$this->request->get['information_id'];
		} else {
			$information_id = 0;
		}

		$information_info = $this->model_catalog_information->getInformation($information_id);

		if ($information_info) {
			
			if ($information_info['meta_title']) {
				$this->document->setTitle($information_info['meta_title']);
			} else {
				$this->document->setTitle($information_info['title']);
			}
			
			if ($information_info['noindex'] <= 0) {
				$this->document->setRobots('noindex,follow');
			}
			
			if ($information_info['meta_h1']) {
				$data['heading_title'] = $information_info['meta_h1'];
			} else {
				$data['heading_title'] = $information_info['title'];
			}
			
			$this->document->setDescription($information_info['meta_description']);
			$this->document->setKeywords($information_info['meta_keyword']);

			$data['breadcrumbs'][] = array(
				'text' => $information_info['title'],
				'href' => $this->url->link('information/information', 'information_id=' .  $information_id)
			);

			$data['description'] = html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8');

			$data['continue'] = $this->url->link('common/home');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('information/information', $data));
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('information/information', 'information_id=' . $information_id)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');

			$data['text_error'] = $this->language->get('text_error');

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}

	public function agree() {
		$this->load->model('catalog/information');

		if (isset($this->request->get['information_id'])) {
			$information_id = (int)$this->request->get['information_id'];
		} else {
			$information_id = 0;
		}

		$output = '';

		$information_info = $this->model_catalog_information->getInformation($information_id);

		if ($information_info) {
			$output .= html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8') . "\n";
		}

		$this->response->setOutput($output);
	}
	public function excel() {
			function getheight_t($str, $size_pole, $height_need) {
				if (strlen($str)>$size_pole-9) {
				$size_h=(((strlen($str))-(strlen($str))%$size_pole)/$size_pole)+1;
				} else $size_h=1;
				return $size_h*$height_need;
			}
        $cwd = getcwd();
        chdir( DIR_SYSTEM.'library/PHPExcel' );
        require_once( 'PHPExcel.php' );
        chdir( $cwd ); //подключили библиотеку
        $phpexcel = new PHPExcel();
        
        $this->load->model('catalog/product');
        $this->load->model('catalog/category');
        $this->load->model('catalog/manufacturer'); //подключили нужные для выгрузки модели

        $filter = array(
            'start' => 0,
            'limit' => 9999999999999
        );
        $products = $this->model_catalog_product->getProducts($filter); //берем все продукты

        $page = $phpexcel->setActiveSheetIndex(0); //создаем вкладку

        $page->getColumnDimensionByColumn("0")->setWidth(20);
        $page->getColumnDimensionByColumn("1")->setWidth(70);
        $page->getColumnDimensionByColumn("2")->setWidth(20);
        $page->getColumnDimensionByColumn("3")->setWidth(20);
        $page->getColumnDimensionByColumn("4")->setWidth(8);
        $page->getColumnDimensionByColumn("5")->setWidth(6);
        $page->getColumnDimensionByColumn("6")->setWidth(12); //задаем ширину столбцов
        
        $page->setCellValue("A1", "Категория КатегорияКатегорияКатегория Категория Категория");
        $page->getStyle("A1")->getAlignment()->setWrapText(true);
       

        $page->setCellValue("B1", "Товар");
        $page->setCellValue("C1", "Производитель");
        $page->setCellValue("D1", "Модель");
        $page->setCellValue("E1", "Артикул");
        $page->setCellValue("F1", "Цена");
        $page->setCellValue("G1", "Количество"); //прописали в первой строке название столбцов

		 $line = 1;  $str = 'dlkfsdklklskf dslkklf sdlkfgklf sdlkfgkldsg lksdgklsd sdkfgkld dlkgkld gdlkgdkld dlkglkdgm dlkgdklg dlkdglkd dlkgldkgm dlkgkldgmlk kdlljfkl  fkdjfkl sdlkjdfkl sdlkfjkld sdlkfklsfdj';
		        $line++;
				$page->setCellValue("A{$line}", 'Примечание:');
				$page->setCellValue("B{$line}", $str);
				
				$page->getStyle("B{$line}")->getAlignment()->setWrapText(true);
				$page->getStyle("C{$line}")->getAlignment()->setWrapText(true);
				$page->getStyle("D{$line}")->getAlignment()->setWrapText(true);
				$page->getStyle("E{$line}")->getAlignment()->setWrapText(true);
				$page->getStyle("F{$line}")->getAlignment()->setWrapText(true);
				$page->getStyle("G{$line}")->getAlignment()->setWrapText(true);
				$page->mergeCells("B{$line}:G{$line}");
				$phpexcel->getActiveSheet()->getRowDimension($line)->setRowHeight(getheight_t($str,136,15));

        
         $i=5;
         foreach($products as $product){ //перебираем массив продуктов и записываем в файл
            // $cats = $this->getPathByProduct($product['product_id']);

            // $cat_text = '';
            
            // $cats_arr = explode("_", $cats);
            // foreach($cats_arr as $category_id){
            //     $category_info = $this->model_catalog_category->getCategory($category_id);
            //     $cat_text .= $category_info['name'] . '/';
            // }
            
            // $cat_text = rtrim($cat_text, "/");
            
            $manufacturer = $this->model_catalog_manufacturer->getManufacturer($product['manufacturer_id']);
            
         //   $page->setCellValue("A$i", $cat_text);
            $page->setCellValue("B$i", $product['name']);
         //   $page->setCellValue("C$i", $manufacturer['name']);
            $page->setCellValue("D$i", $product['model']);
            $page->setCellValue("E$i", $product['sku']);
            $page->setCellValue("F$i", $product['special']?$product['special']:$product['price']);
            $page->setCellValue("G$i", $product['quantity']);
              
            $i++;            
        }
        
        $page->setTitle("Товары");
            
        $filename = DIR_SYSTEM . 'products.xlsx';

          $objWriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
            $objWriter->save($filename);
            echo $filename;
            echo "<p>Файл создан! <a href='/wfm_export_orders/file.xlsx' class='text-danger'>Скачать</a></p>";
       
        die;
        // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        // header('Content-Disposition: attachment;filename="'.$filename.'"');
        // header('Cache-Control: max-age=0');
        // $objWriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
        // $objWriter->setPreCalculateFormulas(false);
        // $objWriter->save('php://output'); //отдаем файл в браузер по ссылке
    }


public function discounts_to_xls() {
			
        $cwd = getcwd();
        chdir( DIR_SYSTEM.'library/PHPExcel' );
        require_once( 'PHPExcel.php' );
        chdir( $cwd ); //подключили библиотеку
        $phpexcel = new PHPExcel();
        
        $this->load->model('catalog/product');
        $this->load->model('catalog/category');
        $this->load->model('catalog/manufacturer'); //подключили нужные для выгрузки модели

        $filter = array(
            'start' => 0,
            'limit' => 9999999999999
        );
        $products = $this->model_catalog_product->getTotalProductDiscounts(); //берем все скидки

        $page = $phpexcel->setActiveSheetIndex(0); //создаем вкладку

        $page->getColumnDimensionByColumn("0")->setWidth(20);
        $page->getColumnDimensionByColumn("1")->setWidth(70);
        $page->getColumnDimensionByColumn("2")->setWidth(20);
        $page->getColumnDimensionByColumn("3")->setWidth(20);
        $page->getColumnDimensionByColumn("4")->setWidth(8);
        $page->getColumnDimensionByColumn("5")->setWidth(6);
        $page->getColumnDimensionByColumn("6")->setWidth(12); //задаем ширину столбцов
        
        $page->setCellValue("A1", "Product_id ");
        $page->getStyle("A1")->getAlignment()->setWrapText(true);
      
        $page->setCellValue("B1", "Кол-во");
        $page->setCellValue("C1", "Цена");
        //$page->setCellValue("D1", "Модель");
        //$page->setCellValue("E1", "Артикул");
        //$page->setCellValue("F1", "Цена");
        //$page->setCellValue("G1", "Количество"); //прописали в первой строке название столбцов

		 $line = 1;  
		 $line++;
				
				
				$page->getStyle("B{$line}")->getAlignment()->setWrapText(true);
				$page->getStyle("C{$line}")->getAlignment()->setWrapText(true);
				$page->getStyle("D{$line}")->getAlignment()->setWrapText(true);
				$page->getStyle("E{$line}")->getAlignment()->setWrapText(true);
				$page->getStyle("F{$line}")->getAlignment()->setWrapText(true);
				$page->getStyle("G{$line}")->getAlignment()->setWrapText(true);
				//$page->mergeCells("B{$line}:G{$line}");
				//$phpexcel->getActiveSheet()->getRowDimension($line)->setRowHeight(getheight_t($str,136,15));

         foreach($products as $product){ //перебираем массив продуктов и записываем в файл
            // $cats = $this->getPathByProduct($product['product_id']);

            // $cat_text = '';
            
            // $cats_arr = explode("_", $cats);
            // foreach($cats_arr as $category_id){
            //     $category_info = $this->model_catalog_category->getCategory($category_id);
            //     $cat_text .= $category_info['name'] . '/';
            // }
            
            // $cat_text = rtrim($cat_text, "/");
            
          //  $manufacturer = $this->model_catalog_manufacturer->getManufacturer($product['manufacturer_id']);
            
         //   $page->setCellValue("A$i", $cat_text);
            $page->setCellValue("A$line", $product['product_id']);
         //   $page->setCellValue("C$i", $manufacturer['name']);
            $page->setCellValue("B$line", $product['quantity']);
            $page->setCellValue("C$line", $product['price']);
           // $page->setCellValue("F$line", $product['special']?$product['special']:$product['price']);
           // $page->setCellValue("G$line", $product['quantity']);
              
            $line++;            
        }
        
        $page->setTitle("Товары");
            
        $filename = DIR_SYSTEM . 'discounts.xlsx';

          $objWriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
            $objWriter->save($filename);
          //  echo $filename;
         //   echo "<p>Файл создан! <a href='/wfm_export_orders/file.xlsx' class='text-danger'>Скачать</a></p>";
       
        die;
        // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        // header('Content-Disposition: attachment;filename="'.$filename.'"');
        // header('Cache-Control: max-age=0');
        // $objWriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
        // $objWriter->setPreCalculateFormulas(false);
        // $objWriter->save('php://output'); //отдаем файл в браузер по ссылке
    }


}