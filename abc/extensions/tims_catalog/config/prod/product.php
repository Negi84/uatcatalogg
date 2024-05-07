<?php
return [
    //'conversion_rate' => 0.005,
    'uplift_categories'      => [
        1 => [
            'desc'           => 'OMNE purchased rewards, including concierge service',
            'manuf_charge'  => 0,
            'assumed_carge' => 10,
            'bidfood'       => 'bidfood',
        ],
        2 => [
            'desc'           => 'OMNE purchased rewards vouchers',
            'manuf_charge'   => 12.50,
            'assumed_carge' => 0,
            'bidfood'       => 'bidfood',
        ],
        3 => [
            'desc'          => 'Transfer orders',
            'manuf_charge'  => 10,
            'assumed_carge' => 0,
            'bidfood'       => 'bidfood',
        ],
        4 => [
            'desc'          => 'Process of orders for free of charge POS',
            'manuf_charge'  => 0,
            'assumed_carge' => 0,
            'bidfood'       => 'bidfood',
        ],
        5 => [
            'desc'          => 'UFS All',
            'manuf_charge'  => 10,
            'assumed_carge' => 0,
            'ufs'           => 'ufs',
        ],
        6 => [
            'desc'          => 'Amazon vouchers only (UFS)',
            'manuf_charge'  => 13,
            'assumed_carge' => 0,
            'ufs'           => 'ufs',
        ],
        7 => [
            'desc'          => 'Apple Products',
            'manuf_charge'  => 10,
            'assumed_carge' => 0,
            'bidfood'       => 'bidfood'
        ],
        8 => [
            'desc'          => 'UFS Eligible Products',
            'manuf_charge'  => 0,
            'assumed_carge' => 0,
            'ufs' => 'ufs'
        ],
    ],
    'postal_charges'         => [
        'I' => [
            'desc'  => 'Items',
            'rates' => [
                '1000' => '6.95',
                '3000' => '10.00',
            ],
        ],
        'V' => [
            'desc'  => 'Vouchers (hard copy delivered via postal services)',
            'rates' => [
                '1000' => '6.95',
            ],
        ],
        'E' => [
            'desc'  => 'E Vouchers (excluding Catering Equipment)',
            'rates' => [
                '1000000' => '3.75',
            ],
        ],
        'C' => [
            'desc'  => 'Special Order',
            'rates' => [
                '1000000' => '0.00',
            ],
        ],
        'B' => [
            'desc'  => 'Barbeque Items',
            'rates' => [
                '1000' => '20.00',
            ],
        ],
    ],
    'licensed_product_types' => ['E'],
    'license_suppliers'      => [
        'CINEMASOCIETY' => [
            'desc'       => 'CINEMASOCIETY',
            'sku-prefix' => 'CINEMA',
            'sites'      => [
                                'bidfood',
                            ]
        ],
        'LASTMINUTE'    => [
            'desc'       => 'LASTMINUTE',
            'sku-prefix' => 'LSM',
            'sites'      => [
                                'bidfood',
                                'ufs'
                            ]
        ],
        'MOTIVATES'     => [
            'desc'       => 'MOTIVATES',
            'sku-prefix' => 'LIF',
            'sites'      => [
                                'bidfood',
                                'ufs'
                            ]
        ],
        'ALLGIFTS'      => [
            'desc'       => 'ALLGIFTS',
            'sku-prefix' => 'ALLGIFTS',
            'sites'      => [
                'bidfood',
                'ufs'
            ]
        ],
        'DIGGERCARD'    => [
            'desc'       => 'DIGGERCARD',
            'sku-prefix' => 'AMZ',
            'sites'      => [
                'ufs',
            ]
        ],
        'ECODESUPPLIER' => [
            'desc'       => 'ECODESUPPLIER',
            'sku-prefix' => 'ECODES',
            'sites'      => [
                'ufs',
            ]
        ],
    ],
];