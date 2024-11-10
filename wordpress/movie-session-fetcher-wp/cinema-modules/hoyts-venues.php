<?php
// ************ HOYTS MODULE ************
// This module is responsible for fetching movie & its sessions from Hoyts Cinema

// list of venues & their codes, required for fetching sessions
$hoyts_venues = [
    // VICTORIA VENUES FOR HOYTS
       (object) [
        'code' => 'BMWCIN',
        'suburb' => 'Broadmeadows',
        'state' => 'VIC'
    ], (object) [
        'code' => 'CHDSTN',
        'suburb' => 'Chadstone',
        'state' => 'VIC'
    ], (object) [
        'code' => 'DOCCIN',
        'suburb' => 'District Docklands',
        'state' => 'VIC'
    ], (object) [
        'code' => 'EASTLN',
        'suburb' => 'Eastland',
        'state' => 'VIC'
    ], (object) [
        'code' => 'FHLCIN',
        'suburb' => 'Forrest Hill',
        'state' => 'VIC'
    ], (object) [
        'code' => 'FRANKS',
        'suburb' => 'Frankston',
        'state' => 'VIC'
    ], (object) [
        'code' => 'GRENSB',
        'suburb' => 'Greensborough',
        'state' => 'VIC'
    ], (object) [
        'code' => 'HIGPNT',
        'suburb' => 'Highpoint',
        'state' => 'VIC'
    ], (object) [
        'code' => 'MCECIN',
        'suburb' => 'Melbourne Central',
        'state' => 'VIC'
    ], (object) [
        'code' => 'NORTHL',
        'suburb' => 'Northland',
        'state' => 'VIC'
    ], (object) [
        'code' => 'VGDCIN',
        'suburb' => 'Victoria Gardens',
        'state' => 'VIC'
    ], (object) [
        'code' => 'TAYLOR',
        'suburb' => 'Watergardens',
        'state' => 'VIC'
    ], (object) [
        'code' => 'BANKTN',
        'suburb' => 'Bankstown',
        'state' => 'NSW'
    ], (object) [
        'code' => 'WESCIN',
        'suburb' => 'Blacktown',
        'state' => 'NSW'
    ], (object) [
        'code' => 'BROADW',
        'suburb' => 'Broadway',
        'state' => 'NSW'
    ], (object) [
        'code' => 'CHARLE',
        'suburb' => 'Charlestown',
        'state' => 'NSW'
    ], (object) [
        'code' => 'CHWOOD',
        'suburb' => 'Chatswood Mandarin',
        'state' => 'NSW'
    ], (object) [
        'code' => 'CWFFLD',
        'suburb' => 'Chatswood Westfield',
        'state' => 'NSW'
    ], (object) [
        'code' => 'CROCIN',
        'suburb' => 'Cronulla',
        'state' => 'NSW'
    ], (object) [
        'code' => 'EGDENS',
        'suburb' => 'Eastgardens',
        'state' => 'NSW'
    ], (object) [
        'code' => 'SHOWGR',
        'suburb' => 'Entertainment Quarter',
        'state' => 'NSW'
    ], (object) [
        'code' => 'ERINAF',
        'suburb' => 'Erina',
        'state' => 'NSW'
    ], (object) [
        'code' => 'GHLCIN',
        'suburb' => 'Green Hills',
        'state' => 'NSW'
    ], (object) [
        'code' => 'MTDRTT',
        'suburb' => 'Mt Druitt',
        'state' => 'NSW'
    ], (object) [
        'code' => 'PENRTH',
        'suburb' => 'Penrith',
        'state' => 'NSW'
    ], (object) [
        'code' => 'TWDCTY',
        'suburb' => 'Tweed City',
        'state' => 'NSW'
    ], (object) [
        'code' => 'WWGCIN',
        'suburb' => 'Warrawong',
        'state' => 'NSW'
    ], (object) [
        'code' => 'WGHMAL',
        'suburb' => 'Warringah Mall',
        'state' => 'NSW'
    ], (object) [
        'code' => 'WETHER',
        'suburb' => 'Wetherill Park',
        'state' => 'NSW'
    ], (object) [
        'code' => 'ARNCIN',
        'suburb' => 'Arndale',
        'state' => 'SA'
    ], (object) [
        'code' => 'NWDCIN',
        'suburb' => 'Norwood',
        'state' => 'SA'
    ], (object) [
        'code' => 'SLYCIN',
        'suburb' => 'Salisbury',
        'state' => 'SA'
    ], (object) [
        'code' => 'TTPLZA',
        'suburb' => 'Tea Tree Plaza',
        'state' => 'SA'
    ], (object) [
        'code' => 'IPSCIN',
        'suburb' => 'Ipswich',
        'state' => 'QLD'
    ], (object) [
        'code' => 'REDCLF',
        'suburb' => 'Redcliffe',
        'state' => 'QLD'
    ], (object) [
        'code' => 'STAFRD',
        'suburb' => 'Stafford',
        'state' => 'QLD'
    ], (object) [
        'code' => 'SUNBNK',
        'suburb' => 'Sunnybank',
        'state' => 'QLD'
    ], (object) [
        'code' => 'BELCON',
        'suburb' => 'Belconnen',
        'state' => 'ACT'
    ], (object) [
        'code' => 'WODENP',
        'suburb' => 'Woden',
        'state' => 'ACT'
    ], (object) [
        'code' => 'MIDCIN',
        'suburb' => 'Midland Gate',
        'state' => 'WA'
    ], (object) [
        'code' => 'BUNCIN',
        'suburb' => 'Bunbury',
        'state' => 'WA'
    ], (object) [
        'code' => 'CAROUS',
        'suburb' => 'Carousel',
        'state' => 'WA'
    ], (object) [
        'code' => 'CURCIN',
        'suburb' => 'Currambine',
        'state' => 'WA'
    ], (object) [
        'code' => 'BOOGDN',
        'suburb' => 'Garden City',
        'state' => 'WA'
    ], (object) [
        'code' => 'JOOCIN',
        'suburb' => 'Joondalup',
        'state' => 'WA'
    ], (object) [
        'code' => 'KYPCIN',
        'suburb' => 'Karrinyup',
        'state' => 'WA'
    ], (object) [
        'code' => 'MILENM',
        'suburb' => 'Milennium',
        'state' => 'WA'
    ], (object) [
        'code' => 'SOUTHL',
        'suburb' => 'Southlands',
        'state' => 'WA'
    ], (object) [
        'code' => 'WARCIN',
        'suburb' => 'Warwick',
        'state' => 'WA'
    ]
    //ADD REST LATER
];