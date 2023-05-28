<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $json = '
            {
	"side1": {
		"row1": {
			"1": "1001",
			"2": "1002",
			"3": "1003",
			"4": "1004",
			"5": "1005"
		},
		"row2": {
			"1": "1006",
			"2": "1007",
			"3": "1008",
			"4": "1009",
			"5": "1010"
		}
	},
	"side2": {
		"row1": {
			"1": "1011",
			"2": "1012",
			"3": "1013",
			"4": "1014",
			"5": "1015"
		},
		"row2": {
			"1": "1016",
			"2": "1017",
			"3": "1018",
			"4": "1019",
			"5": "1021"
		}
	}
}
        ';
        $jd = json_decode($json,true);
        $format = '<div class="row">';
        foreach ($jd as $side) {
            $format .= '<div class="col">';
            foreach ($side as $row) {
                $format .= '<div class="row">';
                foreach ($row as $slot) {
                    $format .= '<div class="col">'.$slot.'</div>';
                }
                $format .= '</div>';
            }
            $format .= '</div>';
        }

        $format .= '</div>';
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'test' => $format
        ]);
    }
}
