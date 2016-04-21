<?php
/*"******************************************************************************************************
*   (c) 2013-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                           *
********************************************************************************************************/
namespace Kajona\Highcharts\System;

use Kajona\System\System\Exception;
use Kajona\System\System\GraphDatapoint;

/**
 * This class contains the data for a series and their specific options.
 *
 * @package module_highcharts
 * @since 4.6
 * @author stefan.meyer1@yahoo.de
 */
class GraphHighchartsSeriesdata
{

    private $arrDataPoints = null;
    private $intChartType = null;
    private $intSeriesDataOrder = null;


    //contains specific options for this series
    private $arrSeriesOptions = array(
        "type"       => null,
        "data"       => null,
        "name"       => null,
        "dataLabels" => array(
            "enabled" => null
        ),

    );


    public function __construct($strChartType, $intSeriesDataOrder, &$arrGlobalOptions)
    {
        $this->intSeriesDataOrder = $intSeriesDataOrder;
        $this->intChartType = $strChartType;

        if($strChartType == GraphHighchartsCharttype::LINE) {
            $this->arrSeriesOptions["type"] = "line";
        }
        else if($strChartType == GraphHighchartsCharttype::BAR) {
            $this->arrSeriesOptions["type"] = "column";
        }
        else if($strChartType == GraphHighchartsCharttype::BAR_HORIZONTAL) {
            $this->arrSeriesOptions["type"] = "bar";
        }
        else if($strChartType == GraphHighchartsCharttype::STACKEDBAR) {
            $this->arrSeriesOptions["type"] = "column";
            $this->arrSeriesOptions["stacking"] = "normal";
            $this->arrSeriesOptions["dataLabels"]["enabled"] = true;

            $arrGlobalOptions["yAxis"]["stackLabels"]["enabled"] = true;
        }
        else if($strChartType == GraphHighchartsCharttype::STACKEDBAR_HORIZONTAL) {
            $this->arrSeriesOptions["type"] = "bar";
            $this->arrSeriesOptions["stacking"] = "normal";
            $this->arrSeriesOptions["dataLabels"]["enabled"] = true;

            $arrGlobalOptions["xAxis"]["stackLabels"]["enabled"] = true;
        }
        else if($strChartType == GraphHighchartsCharttype::PIE) {
            $this->arrSeriesOptions["type"] = "pie";
        }
        else {
            throw new Exception("Not a valid chart type", Exception::$level_ERROR);
        }
    }


    /**
     * @param bool $bitWriteValues
     */
    public function setBitWriteValues($bitWriteValues = false)
    {
        $this->arrSeriesOptions["dataLabels"]["enabled"] = $bitWriteValues;
    }

    /**
     * @return int
     */
    public function getIntChartType()
    {
        return $this->intChartType;
    }


    /**
     * @return int
     */
    public function getIntSeriesDataOrder()
    {
        return $this->intSeriesDataOrder;
    }


    /**
     * @param array $arrDataArray
     */
    public function setArrDataPoints($arrDataArray)
    {
        $this->arrDataPoints = $arrDataArray;

        //now process array -> all values which are not numeric will be converted to a 0
        foreach($this->arrDataPoints as $objDataPoint) {
            if(!is_numeric($objDataPoint->getFloatValue())) {
                $objDataPoint->setFloatValue(0);
            }
        }

        if(count($this->arrDataPoints) == 0) {
            $this->arrDataPoints = array(new GraphDatapoint(0));
        }
    }

    /**
     * @return GraphDatapoint[]
     */
    public function getArrDataPoints()
    {
        return $this->arrDataPoints;
    }

    /**
     * @param string $strSeriesLabel
     */
    public function setStrSeriesLabel($strSeriesLabel)
    {
        $this->arrSeriesOptions["name"] = $strSeriesLabel;
    }

    /**
     * @return string
     */
    public function getStrSeriesLabel()
    {
        return $this->arrSeriesOptions["name"];
    }

    /**
     * Converts the php array to a JSON string for jqplot
     *
     * @return string
     */
    public function optionsToJSON()
    {
        return json_encode($this->arrSeriesOptions);
    }

    /**
     * @return array
     */
    public function getArrSeriesOptions()
    {
        return $this->arrSeriesOptions;
    }

    /**
     * @param array $arrSeriesOptions
     */
    public function setArrSeriesOptions($arrSeriesOptions)
    {
        $this->arrSeriesOptions = $arrSeriesOptions;
    }
}