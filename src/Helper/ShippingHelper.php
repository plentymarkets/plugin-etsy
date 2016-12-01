<?php

namespace Etsy\Helper;

use Plenty\Modules\Accounting\Contracts\AccountingServiceContract;
use Plenty\Plugin\Application;

class ShippingHelper
{
	private $carriers = [
		'4px'                                  => [
			'id'        => '4px',
			'name'      => '4PX Worldwide Express',
			'trackable' => true,
		],
		'abf'                                  => [
			'id'        => 'abf',
			'name'      => 'ABF Freight',
			'trackable' => true,
		],
		'acscourier'                           => [
			'id'        => 'acscourier',
			'name'      => 'ACS Courier',
			'trackable' => true,
		],
		'apc'                                  => [
			'id'        => 'apc',
			'name'      => 'APC Postal Logistics',
			'trackable' => true,
		],
		'aeroflash'                            => [
			'id'        => 'aeroflash',
			'name'      => 'AeroFlash',
			'trackable' => true,
		],
		'afghan-post'                          => [
			'id'        => 'afghan-post',
			'name'      => 'Afghan Post',
			'trackable' => false,
		],
		'an-post'                              => [
			'id'        => 'an-post',
			'name'      => 'An Post',
			'trackable' => true,
		],
		'anguilla-post'                        => [
			'id'        => 'anguilla-post',
			'name'      => 'Anguilla Postal Service',
			'trackable' => false,
		],
		'aramex'                               => [
			'id'        => 'aramex',
			'name'      => 'Aramex',
			'trackable' => true,
		],
		'asendia-usa'                          => [
			'id'        => 'asendia-usa',
			'name'      => 'Asendia USA',
			'trackable' => true,
		],
		'australia-post'                       => [
			'id'        => 'australia-post',
			'name'      => 'Australia Post',
			'trackable' => true,
		],
		'austrian-post'                        => [
			'id'        => 'austrian-post',
			'name'      => 'Austrian Post',
			'trackable' => true,
		],
		'austrian-post-registered'             => [
			'id'        => 'austrian-post-registered',
			'name'      => 'Austrian Post Registered',
			'trackable' => true,
		],
		'bh-posta'                             => [
			'id'        => 'bh-posta',
			'name'      => 'BH Posta',
			'trackable' => false,
		],
		'bahrain-post'                         => [
			'id'        => 'bahrain-post',
			'name'      => 'Bahrain Post',
			'trackable' => false,
		],
		'bangladesh-post'                      => [
			'id'        => 'bangladesh-post',
			'name'      => 'Bangladesh Post Office',
			'trackable' => false,
		],
		'bpost'                                => [
			'id'        => 'bpost',
			'name'      => 'Belgium Post Domestic',
			'trackable' => true,
		],
		'bpost-international'                  => [
			'id'        => 'bpost-international',
			'name'      => 'Belgium Post International',
			'trackable' => true,
		],
		'belpost'                              => [
			'id'        => 'belpost',
			'name'      => 'Belposhta',
			'trackable' => true,
		],
		'bluedart'                             => [
			'id'        => 'bluedart',
			'name'      => 'Blue Dart',
			'trackable' => true,
		],
		'botswanapost'                         => [
			'id'        => 'botswanapost',
			'name'      => 'BotswanaPost',
			'trackable' => false,
		],
		'brunei-post'                          => [
			'id'        => 'brunei-post',
			'name'      => 'Brunei Postal Services',
			'trackable' => false,
		],
		'bgpost'                               => [
			'id'        => 'bgpost',
			'name'      => 'Bulgarian Posts',
			'trackable' => true,
		],
		'cambodia-post'                        => [
			'id'        => 'cambodia-post',
			'name'      => 'Cambodia Post',
			'trackable' => true,
		],
		'canadapost'                           => [
			'id'        => 'canadapost',
			'name'      => 'Canada Post',
			'trackable' => true,
		],
		'ceska-posta'                          => [
			'id'        => 'ceska-posta',
			'name'      => 'Ceska Posta',
			'trackable' => true,
		],
		'china-ems'                            => [
			'id'        => 'china-ems',
			'name'      => 'China EMS',
			'trackable' => true,
		],
		'china-post'                           => [
			'id'        => 'china-post',
			'name'      => 'China Post',
			'trackable' => true,
		],
		'chronopost-france'                    => [
			'id'        => 'chronopost-france',
			'name'      => 'Chronopost France',
			'trackable' => true,
		],
		'chronopost-portugal'                  => [
			'id'        => 'chronopost-portugal',
			'name'      => 'Chronopost Portugal',
			'trackable' => true,
		],
		'taiwan-post'                          => [
			'id'        => 'taiwan-post',
			'name'      => 'Chunghwa Post',
			'trackable' => true,
		],
		'city-link'                            => [
			'id'        => 'city-link',
			'name'      => 'City Link',
			'trackable' => true,
		],
		'colissimo'                            => [
			'id'        => 'colissimo',
			'name'      => 'Colissimo',
			'trackable' => true,
		],
		'brazil-correios'                      => [
			'id'        => 'brazil-correios',
			'name'      => 'Correios de Brasil',
			'trackable' => true,
		],
		'correios-macau'                       => [
			'id'        => 'correios-macau',
			'name'      => 'Correios de Macau',
			'trackable' => false,
		],
		'portugal-ctt'                         => [
			'id'        => 'portugal-ctt',
			'name'      => 'Correios de Portugal (CTT)',
			'trackable' => true,
		],
		'correo-argentino'                     => [
			'id'        => 'correo-argentino',
			'name'      => 'Correo Argentino Domestic',
			'trackable' => true,
		],
		'correo-argentino-intl'                => [
			'id'        => 'correo-argentino-intl',
			'name'      => 'Correo Argentino International',
			'trackable' => true,
		],
		'correo-uruguayo'                      => [
			'id'        => 'correo-uruguayo',
			'name'      => 'Correo Uruguayo',
			'trackable' => false,
		],
		'spain-correos-es'                     => [
			'id'        => 'spain-correos-es',
			'name'      => 'Correos - Espana',
			'trackable' => true,
		],
		'correos-chile'                        => [
			'id'        => 'correos-chile',
			'name'      => 'Correos Chile',
			'trackable' => true,
		],
		'correos-de-mexico'                    => [
			'id'        => 'correos-de-mexico',
			'name'      => 'Correos De Mexico',
			'trackable' => true,
		],
		'correos-costa-rica'                   => [
			'id'        => 'correos-costa-rica',
			'name'      => 'Correos de Costa Rica',
			'trackable' => false,
		],
		'correos-ecuador'                      => [
			'id'        => 'correos-ecuador',
			'name'      => 'Correos del Ecuador',
			'trackable' => false,
		],
		'courierpost'                          => [
			'id'        => 'courierpost',
			'name'      => 'Courier Post',
			'trackable' => true,
		],
		'couriers-please'                      => [
			'id'        => 'couriers-please',
			'name'      => 'Couriers Please',
			'trackable' => true,
		],
		'cyprus-post'                          => [
			'id'        => 'cyprus-post',
			'name'      => 'Cyprus Post',
			'trackable' => true,
		],
		'dhl'                                  => [
			'id'        => 'dhl',
			'name'      => 'DHL',
			'trackable' => true,
		],
		'dhl-benelux'                          => [
			'id'        => 'dhl-benelux',
			'name'      => 'DHL Benelux',
			'trackable' => true,
		],
		'dhl-germany'                          => [
			'id'        => 'dhl-germany',
			'name'      => 'DHL Germany',
			'trackable' => true,
		],
		'dhl-global-mail'                      => [
			'id'        => 'dhl-global-mail',
			'name'      => 'DHL Global Mail',
			'trackable' => true,
		],
		'dhl-global-mail-asia'                 => [
			'id'        => 'dhl-global-mail-asia',
			'name'      => 'DHL Global Mail Asia',
			'trackable' => true,
		],
		'dhl-nl'                               => [
			'id'        => 'dhl-nl',
			'name'      => 'DHL Netherlands',
			'trackable' => true,
		],
		'dhl-poland'                           => [
			'id'        => 'dhl-poland',
			'name'      => 'DHL Polska',
			'trackable' => true,
		],
		'dpd'                                  => [
			'id'        => 'dpd',
			'name'      => 'DPD',
			'trackable' => true,
		],
		'dpd-de'                               => [
			'id'        => 'dpd-de',
			'name'      => 'DPD Germany',
			'trackable' => true,
		],
		'dpd-poland'                           => [
			'id'        => 'dpd-poland',
			'name'      => 'DPD Polska',
			'trackable' => true,
		],
		'dpd-uk'                               => [
			'id'        => 'dpd-uk',
			'name'      => 'DPD UK',
			'trackable' => true,
		],
		'dtdc'                                 => [
			'id'        => 'dtdc',
			'name'      => 'DTDC India',
			'trackable' => true,
		],
		'deltec-courier'                       => [
			'id'        => 'deltec-courier',
			'name'      => 'Deltec Courier',
			'trackable' => true,
		],
		'deutsch-post'                         => [
			'id'        => 'deutsch-post',
			'name'      => 'Deutsche Post',
			'trackable' => true,
		],
		'directlink'                           => [
			'id'        => 'directlink',
			'name'      => 'Direct Link',
			'trackable' => true,
		],
		'ec-firstclass'                        => [
			'id'        => 'ec-firstclass',
			'name'      => 'EC-Firstclass',
			'trackable' => true,
		],
		'egypt-post'                           => [
			'id'        => 'egypt-post',
			'name'      => 'Egypt Post',
			'trackable' => false,
		],
		'el-correo'                            => [
			'id'        => 'el-correo',
			'name'      => 'El Correo',
			'trackable' => false,
		],
		'elta-courier'                         => [
			'id'        => 'elta-courier',
			'name'      => 'Elta Courier',
			'trackable' => true,
		],
		'emirates-post'                        => [
			'id'        => 'emirates-post',
			'name'      => 'Empost',
			'trackable' => true,
		],
		'correos-bolivia'                      => [
			'id'        => 'correos-bolivia',
			'name'      => 'Empresa de Correos de Bolivia',
			'trackable' => false,
		],
		'estafeta'                             => [
			'id'        => 'estafeta',
			'name'      => 'Estafeta',
			'trackable' => true,
		],
		'estes'                                => [
			'id'        => 'estes',
			'name'      => 'Estes',
			'trackable' => true,
		],
		'estonian-post'                        => [
			'id'        => 'estonian-post',
			'name'      => 'Estonian Post',
			'trackable' => false,
		],
		'ethiopian-post'                       => [
			'id'        => 'ethiopian-post',
			'name'      => 'Ethiopian Postal Service',
			'trackable' => false,
		],
		'evergreen'                            => [
			'id'        => 'evergreen',
			'name'      => 'Evergreen',
			'trackable' => true,
		],
		'fastway-au'                           => [
			'id'        => 'fastway-au',
			'name'      => 'Fastway Australia',
			'trackable' => true,
		],
		'fastway-ireland'                      => [
			'id'        => 'fastway-ireland',
			'name'      => 'Fastway Couriers',
			'trackable' => true,
		],
		'fastway-za'                           => [
			'id'        => 'fastway-za',
			'name'      => 'Fastways Couriers South Africa',
			'trackable' => true,
		],
		'fedex'                                => [
			'id'        => 'fedex',
			'name'      => 'FedEx',
			'trackable' => true,
		],
		'fedex-uk'                             => [
			'id'        => 'fedex-uk',
			'name'      => 'Fedex UK (Domestic)',
			'trackable' => true,
		],
		'first-flight'                         => [
			'id'        => 'first-flight',
			'name'      => 'First Flight Couriers',
			'trackable' => true,
		],
		'flash-courier'                        => [
			'id'        => 'flash-courier',
			'name'      => 'Flash Courier',
			'trackable' => true,
		],
		'gati-kwe'                             => [
			'id'        => 'gati-kwe',
			'name'      => 'GATI-KWE',
			'trackable' => true,
		],
		'gls'                                  => [
			'id'        => 'gls',
			'name'      => 'GLS',
			'trackable' => true,
		],
		'ghana-post'                           => [
			'id'        => 'ghana-post',
			'name'      => 'Ghana Post',
			'trackable' => false,
		],
		'greyhound'                            => [
			'id'        => 'greyhound',
			'name'      => 'Greyhound',
			'trackable' => true,
		],
		'guernsey-post'                        => [
			'id'        => 'guernsey-post',
			'name'      => 'Guernsey Post',
			'trackable' => false,
		],
		'hay-post'                             => [
			'id'        => 'hay-post',
			'name'      => 'Hay Post',
			'trackable' => false,
		],
		'hellenic-post'                        => [
			'id'        => 'hellenic-post',
			'name'      => 'Hellenic Post',
			'trackable' => false,
		],
		'hermes'                               => [
			'id'        => 'hermes',
			'name'      => 'Hermes',
			'trackable' => true,
		],
		'hermes-de'                            => [
			'id'        => 'hermes-de',
			'name'      => 'Hermes Germany',
			'trackable' => true,
		],
		'hong-kong-post'                       => [
			'id'        => 'hong-kong-post',
			'name'      => 'Hong Kong Post',
			'trackable' => true,
		],
		'hrvatska-posta'                       => [
			'id'        => 'hrvatska-posta',
			'name'      => 'Hrvatska Posta',
			'trackable' => true,
		],
		'india-post'                           => [
			'id'        => 'india-post',
			'name'      => 'India Post',
			'trackable' => true,
		],
		'india-post-int'                       => [
			'id'        => 'india-post-int',
			'name'      => 'India Post International',
			'trackable' => true,
		],
		'interlink-express'                    => [
			'id'        => 'interlink-express',
			'name'      => 'Interlink Express',
			'trackable' => true,
		],
		'international-seur'                   => [
			'id'        => 'international-seur',
			'name'      => 'International Seur',
			'trackable' => true,
		],
		'ipostel'                              => [
			'id'        => 'ipostel',
			'name'      => 'Ipostel',
			'trackable' => false,
		],
		'iran-post'                            => [
			'id'        => 'iran-post',
			'name'      => 'Iran Post',
			'trackable' => false,
		],
		'islandspostur'                        => [
			'id'        => 'islandspostur',
			'name'      => 'Islandspostur',
			'trackable' => false,
		],
		'isle-of-man-post'                     => [
			'id'        => 'isle-of-man-post',
			'name'      => 'Isle of Man Post Office',
			'trackable' => false,
		],
		'israel-post'                          => [
			'id'        => 'israel-post',
			'name'      => 'Israel Post',
			'trackable' => true,
		],
		'israel-post-domestic'                 => [
			'id'        => 'israel-post-domestic',
			'name'      => 'Israel Post Domestic',
			'trackable' => true,
		],
		'jamaica-post'                         => [
			'id'        => 'jamaica-post',
			'name'      => 'Jamaica Post',
			'trackable' => false,
		],
		'japan-post'                           => [
			'id'        => 'japan-post',
			'name'      => 'Japan Post',
			'trackable' => true,
		],
		'jersey-post'                          => [
			'id'        => 'jersey-post',
			'name'      => 'Jersey Post',
			'trackable' => false,
		],
		'jordan-post'                          => [
			'id'        => 'jordan-post',
			'name'      => 'Jordan Post',
			'trackable' => false,
		],
		'kazpost'                              => [
			'id'        => 'kazpost',
			'name'      => 'Kazpost',
			'trackable' => false,
		],
		'korea-post'                           => [
			'id'        => 'korea-post',
			'name'      => 'Korea Post',
			'trackable' => true,
		],
		'kn'                                   => [
			'id'        => 'kn',
			'name'      => 'Kuehne + Nagel',
			'trackable' => true,
		],
		'la-poste-colissimo'                   => [
			'id'        => 'la-poste-colissimo',
			'name'      => 'La Poste',
			'trackable' => true,
		],
		'poste-monaco'                         => [
			'id'        => 'poste-monaco',
			'name'      => 'La Poste Monaco',
			'trackable' => false,
		],
		'poste-tunisienne'                     => [
			'id'        => 'poste-tunisienne',
			'name'      => 'La Poste Tunisienne',
			'trackable' => false,
		],
		'poste-senegal'                        => [
			'id'        => 'poste-senegal',
			'name'      => 'La Poste du Senegal',
			'trackable' => false,
		],
		'lasership'                            => [
			'id'        => 'lasership',
			'name'      => 'LaserShip',
			'trackable' => true,
		],
		'latvijas-pasts'                       => [
			'id'        => 'latvijas-pasts',
			'name'      => 'Latvijas Pasts',
			'trackable' => false,
		],
		'libanpost'                            => [
			'id'        => 'libanpost',
			'name'      => 'LibanPost',
			'trackable' => false,
		],
		'lietuvos-pastas'                      => [
			'id'        => 'lietuvos-pastas',
			'name'      => 'Lietuvos Pastas',
			'trackable' => true,
		],
		'mrw-spain'                            => [
			'id'        => 'mrw-spain',
			'name'      => 'MRW',
			'trackable' => true,
		],
		'magyar-posta'                         => [
			'id'        => 'magyar-posta',
			'name'      => 'Magyar Posta',
			'trackable' => true,
		],
		'makedonska-posta'                     => [
			'id'        => 'makedonska-posta',
			'name'      => 'Makedonska Posta',
			'trackable' => false,
		],
		'malaysia-post-posdaftar'              => [
			'id'        => 'malaysia-post-posdaftar',
			'name'      => 'Malaysia Pos Daftar',
			'trackable' => true,
		],
		'maldives-post'                        => [
			'id'        => 'maldives-post',
			'name'      => 'Maldives Post',
			'trackable' => false,
		],
		'maltapost'                            => [
			'id'        => 'maltapost',
			'name'      => 'MaltaPost',
			'trackable' => false,
		],
		'mauritius-post'                       => [
			'id'        => 'mauritius-post',
			'name'      => 'Mauritius Post',
			'trackable' => false,
		],
		'mexico-multipack'                     => [
			'id'        => 'mexico-multipack',
			'name'      => 'Multipack',
			'trackable' => true,
		],
		'nacex-spain'                          => [
			'id'        => 'nacex-spain',
			'name'      => 'Nacex',
			'trackable' => true,
		],
		'new-zealand-post'                     => [
			'id'        => 'new-zealand-post',
			'name'      => 'New Zealand Post',
			'trackable' => true,
		],
		'nieuwe-post-nederlandse-antillen-pna' => [
			'id'        => 'nieuwe-post-nederlandse-antillen-pna',
			'name'      => 'Nieuwe Post Nederlandse Antillen (PNA)',
			'trackable' => false,
		],
		'nipost'                               => [
			'id'        => 'nipost',
			'name'      => 'Nigerian Postal Service',
			'trackable' => true,
		],
		'nova-poshta'                          => [
			'id'        => 'nova-poshta',
			'name'      => 'Nova Poshta',
			'trackable' => true,
		],
		'oca-ar'                               => [
			'id'        => 'oca-ar',
			'name'      => 'OCA',
			'trackable' => true,
		],
		'opek'                                 => [
			'id'        => 'opek',
			'name'      => 'OPEK',
			'trackable' => true,
		],
		'opt'                                  => [
			'id'        => 'opt',
			'name'      => 'OPT',
			'trackable' => false,
		],
		'opt-nouvelle-caledonie'               => [
			'id'        => 'opt-nouvelle-caledonie',
			'name'      => 'OPT de Nouvelle-Caledonie',
			'trackable' => false,
		],
		'oman-post'                            => [
			'id'        => 'oman-post',
			'name'      => 'Oman Post',
			'trackable' => false,
		],
		'ontrac'                               => [
			'id'        => 'ontrac',
			'name'      => 'OnTrac',
			'trackable' => true,
		],
		'ptt-posta'                            => [
			'id'        => 'ptt-posta',
			'name'      => 'PTT Posta',
			'trackable' => true,
		],
		'pakistan-post'                        => [
			'id'        => 'pakistan-post',
			'name'      => 'Pakistan Post',
			'trackable' => false,
		],
		'parcel-force'                         => [
			'id'        => 'parcel-force',
			'name'      => 'Parcelforce Worldwide',
			'trackable' => true,
		],
		'poczta-polska'                        => [
			'id'        => 'poczta-polska',
			'name'      => 'Poczta Polska',
			'trackable' => true,
		],
		'pos-indonesia'                        => [
			'id'        => 'pos-indonesia',
			'name'      => 'Pos Indonesia',
			'trackable' => true,
		],
		'pos-indonesia-int'                    => [
			'id'        => 'pos-indonesia-int',
			'name'      => 'Pos Indonesia International',
			'trackable' => true,
		],
		'malaysia-post'                        => [
			'id'        => 'malaysia-post',
			'name'      => 'Pos Malaysia',
			'trackable' => true,
		],
		'post-aruba'                           => [
			'id'        => 'post-aruba',
			'name'      => 'Post Aruba',
			'trackable' => false,
		],
		'danmark-post'                         => [
			'id'        => 'danmark-post',
			'name'      => 'Post Danmark',
			'trackable' => true,
		],
		'post-fiji'                            => [
			'id'        => 'post-fiji',
			'name'      => 'Post Fiji',
			'trackable' => false,
		],
		'post-luxembourg'                      => [
			'id'        => 'post-luxembourg',
			'name'      => 'Post Luxembourg',
			'trackable' => false,
		],
		'postnl'                               => [
			'id'        => 'postnl',
			'name'      => 'PostNL Domestic',
			'trackable' => true,
		],
		'postnl-international'                 => [
			'id'        => 'postnl-international',
			'name'      => 'PostNL International',
			'trackable' => true,
		],
		'postnl-3s'                            => [
			'id'        => 'postnl-3s',
			'name'      => 'PostNL International 3S',
			'trackable' => true,
		],
		'postnord'                             => [
			'id'        => 'postnord',
			'name'      => 'PostNord Logistics',
			'trackable' => true,
		],
		'posta'                                => [
			'id'        => 'posta',
			'name'      => 'Posta',
			'trackable' => false,
		],
		'posta-kenya'                          => [
			'id'        => 'posta-kenya',
			'name'      => 'Posta Kenya',
			'trackable' => false,
		],
		'posta-moldovei'                       => [
			'id'        => 'posta-moldovei',
			'name'      => 'Posta Moldovei',
			'trackable' => false,
		],
		'posta-romana'                         => [
			'id'        => 'posta-romana',
			'name'      => 'Posta Romana',
			'trackable' => true,
		],
		'posta-shqiptare'                      => [
			'id'        => 'posta-shqiptare',
			'name'      => 'Posta Shqiptare',
			'trackable' => false,
		],
		'posta-slovenije'                      => [
			'id'        => 'posta-slovenije',
			'name'      => 'Posta Slovenije',
			'trackable' => false,
		],
		'posta-srbije'                         => [
			'id'        => 'posta-srbije',
			'name'      => 'Posta Srbije',
			'trackable' => false,
		],
		'posta-uganda'                         => [
			'id'        => 'posta-uganda',
			'name'      => 'Posta Uganda',
			'trackable' => false,
		],
		'poste-italiane'                       => [
			'id'        => 'poste-italiane',
			'name'      => 'Poste Italiane',
			'trackable' => true,
		],
		'poste-italiane-paccocelere'           => [
			'id'        => 'poste-italiane-paccocelere',
			'name'      => 'Poste Italiane Paccocelere',
			'trackable' => true,
		],
		'poste-maroc'                          => [
			'id'        => 'poste-maroc',
			'name'      => 'Poste Maroc',
			'trackable' => false,
		],
		'sweden-posten'                        => [
			'id'        => 'sweden-posten',
			'name'      => 'Posten AB',
			'trackable' => true,
		],
		'posten-norge'                         => [
			'id'        => 'posten-norge',
			'name'      => 'Posten Norge',
			'trackable' => true,
		],
		'posti'                                => [
			'id'        => 'posti',
			'name'      => 'Posti',
			'trackable' => true,
		],
		'postmates'                            => [
			'id'        => 'postmates',
			'name'      => 'Postmates',
			'trackable' => true,
		],
		'purolator'                            => [
			'id'        => 'purolator',
			'name'      => 'Purolator',
			'trackable' => true,
		],
		'qatar-post'                           => [
			'id'        => 'qatar-post',
			'name'      => 'Qatar Post',
			'trackable' => false,
		],
		'rl-carriers'                          => [
			'id'        => 'rl-carriers',
			'name'      => 'RL Carriers',
			'trackable' => true,
		],
		'rpx'                                  => [
			'id'        => 'rpx',
			'name'      => 'RPX Indonesia',
			'trackable' => true,
		],
		'red-express'                          => [
			'id'        => 'red-express',
			'name'      => 'Red Express',
			'trackable' => true,
		],
		'mexico-redpack'                       => [
			'id'        => 'mexico-redpack',
			'name'      => 'Redpack',
			'trackable' => true,
		],
		'royal-mail'                           => [
			'id'        => 'royal-mail',
			'name'      => 'Royal Mail',
			'trackable' => true,
		],
		'russian-post'                         => [
			'id'        => 'russian-post',
			'name'      => 'Russian Post',
			'trackable' => true,
		],
		'italy-sda'                            => [
			'id'        => 'italy-sda',
			'name'      => 'SDA Express Courier',
			'trackable' => true,
		],
		'spanish-seur'                         => [
			'id'        => 'spanish-seur',
			'name'      => 'SEUR Espana (Domestico)',
			'trackable' => true,
		],
		'portugal-seur'                        => [
			'id'        => 'portugal-seur',
			'name'      => 'SEUR Portugal (Domestico)',
			'trackable' => true,
		],
		'sf-express'                           => [
			'id'        => 'sf-express',
			'name'      => 'SF Express',
			'trackable' => true,
		],
		'safexpress'                           => [
			'id'        => 'safexpress',
			'name'      => 'Safexpress',
			'trackable' => true,
		],
		'sagawa'                               => [
			'id'        => 'sagawa',
			'name'      => 'Sagawa',
			'trackable' => true,
		],
		'saudi-post'                           => [
			'id'        => 'saudi-post',
			'name'      => 'Saudi Post',
			'trackable' => true,
		],
		'selektvracht'                         => [
			'id'        => 'selektvracht',
			'name'      => 'Selektvracht',
			'trackable' => true,
		],
		'mexico-senda-express'                 => [
			'id'        => 'mexico-senda-express',
			'name'      => 'Senda Express',
			'trackable' => true,
		],
		'serpost'                              => [
			'id'        => 'serpost',
			'name'      => 'Serpost',
			'trackable' => false,
		],
		'singapore-post'                       => [
			'id'        => 'singapore-post',
			'name'      => 'Singapore Post',
			'trackable' => true,
		],
		'singapore-speedpost'                  => [
			'id'        => 'singapore-speedpost',
			'name'      => 'Singapore SpeedPost',
			'trackable' => true,
		],
		'siodemka'                             => [
			'id'        => 'siodemka',
			'name'      => 'Siodemka',
			'trackable' => true,
		],
		'skynetworldwide'                      => [
			'id'        => 'skynetworldwide',
			'name'      => 'Skynet Worldwide Express',
			'trackable' => true,
		],
		'skynet-malaysia'                      => [
			'id'        => 'skynet-malaysia',
			'name'      => 'Skynet Malaysia',
			'trackable' => true,
		],
		'slovenska-posta'                      => [
			'id'        => 'slovenska-posta',
			'name'      => 'Slovenska posta',
			'trackable' => false,
		],
		'sapo'                                 => [
			'id'        => 'sapo',
			'name'      => 'South Africa Post Office',
			'trackable' => true,
		],
		'star-track'                           => [
			'id'        => 'star-track',
			'name'      => 'StarTrack',
			'trackable' => true,
		],
		'swiss-post'                           => [
			'id'        => 'swiss-post',
			'name'      => 'Swiss Post',
			'trackable' => true,
		],
		'taqbin-hk'                            => [
			'id'        => 'taqbin-hk',
			'name'      => 'TA-Q-BIN Hong Kong',
			'trackable' => true,
		],
		'taqbin-jp'                            => [
			'id'        => 'taqbin-jp',
			'name'      => 'TA-Q-BIN Japan',
			'trackable' => true,
		],
		'taqbin-my'                            => [
			'id'        => 'taqbin-my',
			'name'      => 'TA-Q-BIN Malaysia',
			'trackable' => true,
		],
		'taqbin-sg'                            => [
			'id'        => 'taqbin-sg',
			'name'      => 'TA-Q-BIN Singapore',
			'trackable' => true,
		],
		'tgx'                                  => [
			'id'        => 'tgx',
			'name'      => 'TGX',
			'trackable' => true,
		],
		'tnt'                                  => [
			'id'        => 'tnt',
			'name'      => 'TNT',
			'trackable' => true,
		],
		'tnt-au'                               => [
			'id'        => 'tnt-au',
			'name'      => 'TNT Australia',
			'trackable' => true,
		],
		'tnt-it'                               => [
			'id'        => 'tnt-it',
			'name'      => 'TNT Italia',
			'trackable' => true,
		],
		'tnt-uk'                               => [
			'id'        => 'tnt-uk',
			'name'      => 'TNT UK',
			'trackable' => true,
		],
		'ttpost'                               => [
			'id'        => 'ttpost',
			'name'      => 'TTPost',
			'trackable' => false,
		],
		'thailand-post'                        => [
			'id'        => 'thailand-post',
			'name'      => 'Thailand Post',
			'trackable' => true,
		],
		'toll-global-express'                  => [
			'id'        => 'toll-global-express',
			'name'      => 'Toll Global Express',
			'trackable' => true,
		],
		'toll-priority'                        => [
			'id'        => 'toll-priority',
			'name'      => 'Toll Priority',
			'trackable' => true,
		],
		'uk-mail'                              => [
			'id'        => 'uk-mail',
			'name'      => 'UK Mail',
			'trackable' => true,
		],
		'ups'                                  => [
			'id'        => 'ups',
			'name'      => 'UPS',
			'trackable' => true,
		],
		'ups-freight'                          => [
			'id'        => 'ups-freight',
			'name'      => 'UPS Freight',
			'trackable' => true,
		],
		'usps'                                 => [
			'id'        => 'usps',
			'name'      => 'USPS',
			'trackable' => true,
		],
		'ukrposhta'                            => [
			'id'        => 'ukrposhta',
			'name'      => 'UkrPoshta',
			'trackable' => true,
		],
		'vanuatu-post'                         => [
			'id'        => 'vanuatu-post',
			'name'      => 'Vanuatu Post',
			'trackable' => false,
		],
		'vnpost'                               => [
			'id'        => 'vnpost',
			'name'      => 'Vietnam Post',
			'trackable' => true,
		],
		'vnpost-ems'                           => [
			'id'        => 'vnpost-ems',
			'name'      => 'Vietnam Post EMS',
			'trackable' => true,
		],
		'xend'                                 => [
			'id'        => 'xend',
			'name'      => 'Xend',
			'trackable' => true,
		],
		'yrc'                                  => [
			'id'        => 'yrc',
			'name'      => 'YRC Freight',
			'trackable' => true,
		],
		'yanwen'                               => [
			'id'        => 'yanwen',
			'name'      => 'Yanwen',
			'trackable' => true,
		],
		'yemen-post'                           => [
			'id'        => 'yemen-post',
			'name'      => 'Yemen Post',
			'trackable' => false,
		],
		'yodel'                                => [
			'id'        => 'yodel',
			'name'      => 'Yodel',
			'trackable' => true,
		],
		'zampost'                              => [
			'id'        => 'zampost',
			'name'      => 'Zampost',
			'trackable' => false,
		],
		'zimpost'                              => [
			'id'        => 'zimpost',
			'name'      => 'Zimpost',
			'trackable' => false,
		],
		'i-parcel'                             => [
			'id'        => 'i-parcel',
			'name'      => 'i-parcel',
			'trackable' => true,
		],
	];

	/**
	 * Get the carrier code.
	 *
	 * @param int $parcelServiceType
	 *
	 * @return mixed|null
	 */
	public function getCarrierCode($parcelServiceType)
	{
		$locationId = pluginApp(AccountingServiceContract::class)->detectLocationId(pluginApp(Application::class)->getPlentyId());

		switch($parcelServiceType)
		{
			case 2 && ($locationId == 1): // DHL + Germany
				return 'dhl-germany';

			case 2 && ($locationId == 21): // DHL + Netherlands
				return 'dhl-nl';

			case 2 && ($locationId == 3 || $locationId == 17): // DHL + Belgium/Luxembourg
				return 'dhl-benelux';

			case 2 && ($locationId == 23): // DHL + Poland
				return 'dhl-poland';

			case 2:
				return 'dhl';

			case 3 && ($locationId == 1): // DPD + Germany
				return 'dpd-de';

			case 3 && ($locationId == 23): // DPD + Poland
				return 'dpd-poland';

			case 3 && ($locationId == 12): // DPD + United Kingdom
				return 'dpd-uk';

			case 3: // DPD
				return 'dpd';

			case 4: // POST_DE
				return 'deutsch-post';

			case 5 && ($locationId == 1): // HERMES + Germany
				return 'hermes-de';

			case 5: // HERMES
				return 'hermes';

			case 6 && ($locationId == 29): // TNT + Australia
				return 'tnt-au';

			case 6 && ($locationId == 15): // TNT + Italy
				return 'tnt-it';

			case 6 && ($locationId == 12): // TNT + United Kingdom
				return 'tnt-uk';

			case 6: // TNT
				return 'tnt';

			case 7 && ($locationId == 12): // FEDEX + United Kingdom
				return 'fedex-uk';

			case 7: // FEDEX
				return 'fedex';

			case 8: // UPS
				return 'ups';

			case 9: // GLS
				return 'gls';

			case 10: // POST_AT
				return 'austrian-post';


			case 17: // SWISSPOST
				return 'swiss-post';

			case 18: // ROYAL_MAIL
				return 'royal-mail';

			case 0: // NONE
			case 1: // SELF
			case 11: // SPEDITION
			case 12: // GEL_EXPRS
			case 13: // T_O_FLEX
			case 14: // DACHSER
			case 15: // SCHENKER
			case 16: // EMS
			case 19: // AMAZONPRIME
			case 20: // CBC_LOGISTIC
			case 21: // NETDESPATCH
			case 99: // ELSE
				return null;
		}
	}
}