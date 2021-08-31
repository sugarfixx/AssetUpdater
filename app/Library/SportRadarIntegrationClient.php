<?php
/**
 * Created by PhpStorm.
 * User: sugarfixx
 * Date: 20/08/2021
 * Time: 14:09
 */

namespace App\Library;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Log;

class SportRadarIntegrationClient
{
    private $baseUrl = 'http://mb-sportradar-integration.vpc2.mnw.no/';
    public $jsonStr = '{
   "Group":"F",
   "Stage":"regular season",
   "Teams":"BG PATHUM UNITED,VIETTEL FC",
   "Title":"Custom clips: BG PATHUM UNITED vs. VIETTEL FC - Best pass - Vertical - Graphics",
   "Venue":"PATHUM THANI STADIUM",
   "Season":"2021",
   "Status":"new",
   "Context":"FMA",
   "GroupId":"54482",
   "MatchId":"103675",
   "MediaId":"103675",
   "TeamsId":"35607,40019",
   "VenueId":"5680",
   "Version":"Graphics",
   "AwayTeam":"VIETTEL FC",
   "Category":"Men\'s Football",
   "FeedType":"Multilateral",
   "Filename":"210702_pathum_viettel_27110156_bestpass_vertical.mp4",
   "Filesize":"30441868",
   "HomeTeam":"BG PATHUM UNITED",
   "MatchDay":"3",
   "MimeType":"video",
   "SeasonId":"81258",
   "SubTitle":"AFC Champions League 2021",
   "TagsDate":"2021-07-02 14:00:00 UTC",
   "TagsType":"sport",
   "TagsYear":"2021",
   "Copyright":"100% AFC, including international sound",
   "EventDate":"2021-07-02",
   "MediaType":"Video",
   "TagsArena":"PATHUM THANI STADIUM",
   "TagsRound":"3",
   "TagsTitle":"BG PATHUM UNITED vs VIETTEL FC",
   "TrackerId":"305091",
   "VenueCity":"Pathum Thani",
   "updatedBy":"AssetUpdater",
   "AudioCodec":"AAC",
   "AwayTeamId":"40019",
   "FileStatus":"ready",
   "HomeTeamId":"35607",
   "SeasonName":"AFC Champions League 2021",
   "TagsLeague":"AFC Champions League",
   "TagsSeason":"2021",
   "UploadHost":"https:\/\/www.mediabank.me\/",
   "VideoWidth":"1080",
   "Application":"library",
   "AudioFormat":"AAC",
   "AudioTracks":"1",
   "Competition":"AFC Champions League",
   "ContentType":"Custom clips",
   "SegmentType":"Best pass",
   "TagsCountry":"Select",
   "TagsKickoff":"2021-07-02 14:00:00 UTC",
   "TagsSubType":"soccer",
   "TagsVersion":"2",
   "VideoFormat":"AVC",
   "VideoHeight":"1920",
   "AudioMapping":"-filter_complex \'apad,channelsplit=channel_layout=stereo[aout1][aout2]\' -map \'[aout1]\' -map \'[aout2]\'",
   "IngestSource":"ADEX_CUSTOMCLIP_SPORTRADAR",
   "MatchRelated":"true",
   "MimeTypeFull":"video\/mp4",
   "ReviewStatus":"new",
   "SegmentTitle":"",
   "TagsAwayTeam":"VIETTEL FC",
   "TagsDomainId":"2768",
   "TagsHomeTeam":"BG PATHUM UNITED",
   "UploadFormId":"166968004",
   "VenueCountry":"Thailand",
   "VideoBitRate":"10116729",
   "CompetitionId":"463",
   "FilesizeProxy":"5629917",
   "VideoScanType":"Progressive",
   "SegmentVersion":"Vertical",
   "TagsExternalId":"27110156",
   "UploadUserName":"j.kljajic@sportradar.com",
   "VideoFrameRate":"25.000",
   "AudioResolution":"",
   "AwayTeamCountry":"Vietnam",
   "CompetitionType":"Club",
   "ExternalMatchId":"27110156",
   "GeneralDuration":"23320",
   "GeneralFileSize":"30441868",
   "HomeTeamCountry":"Thailand",
   "NumberOfPosters":"11",
   "TagsHappeningId":"103675",
   "TagsLeagueShort":"",
   "UploadUserEmail":"j.kljajic@sportradar.com",
   "UploadUserPhone":null,
   "VideoAspectRatio":"0.562",
   "AudioSamplingRate":"48000",
   "CompetitionGender":"Male",
   "PublicationStatus":"Published",
   "TagsAwayTeamShort":"VIE",
   "TagsHomeTeamShort":"PAU",
   "UploadCompanyName":"Sportradar",
   "VideoHeightOffset":"",
   "UploadUserFullName":"Josip Kljajic",
   "VideoFormatProfile":"Main@L4.1",
   "VideoOriginalWidth":"",
   "AwayTeamCountryCode":"VNM",
   "FileStorageLocation":"SPORTRADAR\/202107\/10275389004",
   "HomeTeamCountryCode":"THA",
   "VideoCommercialName":"AVC",
   "VideoOriginalHeight":"",
   "AwayTeamAbbreviation":"VIE",
   "GeneralFileExtension":"mp4",
   "GeneralFormatProfile":"Base Media \/ Version 2",
   "GeneralTimeCodeStart":"0",
   "HomeTeamAbbreviation":"PAU",
   "PlayerStartTimestamp":"1625176800",
   "GeneralOverallBitRate":"10443179",
   "VideoOriginalScanType":"",
   "VideoWritingApplication":"",
   "ReviewNotificationListId":null,
   "GeneralOverallBitRateMode":"",
   "AudioChannelsInFirstStream":"2",
   "AutoProcessedDeliveryRules":"1",
   "VideoEncodedApplicationName":"",
   "AutoProcessedHighlightsRules":"1",
   "VideoEncodedApplicationVersion":"",
   "AutoProcessedDeliveryRulesORIGINAL":"1",
   "VideoEncodedApplicationCompanyName":"",
   "SmartSearchNotificationStatuslibrary":"1"
}';
    public function getMetadata($assetMeta)
    {
        $client = new Client();
        $request = [
            'headers' => [
                // 'accept' => 'application/json',
                'Content-Type'=> 'application/json'
            ],
            'json' => [
                'body' => $assetMeta
            ]
        ];
        $request = ['body' => $assetMeta];
        try {
            $response = $client->request('POST',$this->baseUrl . 'metadata', $request);
            if ($response->getStatusCode()== 200 ) {
                $body = (string)$response->getBody();
                if ($body)  {
                    return json_decode($body);
                }

            } else {
                return false;
            }
        } catch (ClientException $e) {

            Log::info($e->getMessage());
        } catch (ServerException $e ) {
            Log::info($e->getMessage());

        } catch (\Exception $e) {
            Log::info($e->getMessage());

        }
    }
}
