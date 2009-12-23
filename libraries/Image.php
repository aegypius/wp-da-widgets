<?php
/* LICENCE
Image.php : Allow to apply various filters on an image.
Copyright (C) 2005  Nicolas LAURENT <innercircle [at] aegypius.com>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/
define('IMAGE_OUTPUTMODE_HTTP'					,1);
define('IMAGE_OUTPUTMODE_FORCEDOWNLOAD'	,2);
define('IMAGE_OUTPUTMODE_FILE'					,4);
define('IMAGE_OUTPUTMODE_BASE64'				,8);

define('IMAGE_RESIZE_NONPROPORTIONAL'		,0);
define('IMAGE_RESIZE_PROPORTIONAL'			,1);

define('IMAGE_ALIGN_TOP'							,	1);
define('IMAGE_ALIGN_BOTTOM'							,	2);
define('IMAGE_ALIGN_LEFT'							,	4);
define('IMAGE_ALIGN_RIGHT'							,	8);
define('IMAGE_ALIGN_CENTER'							,16);
define('IMAGE_ALIGN_MIDDLE'							,32);

define('IMAGE_OUTPUT_PNG'								,IMG_PNG);
define('IMAGE_OUTPUT_GIF'								,IMG_GIF);
define('IMAGE_OUTPUT_JPG'								,IMG_JPG);

define('IMAGE_CACHE_DIRECTORY'					, "cache/");

class Image
{
	function Blur(&$imgResource, $iRadius = 1)
	{
		$iRadius = round(max(0, min($iRadius, 50)) * 2);
		if (!$iRadius) 
			return false;
		
		$w = ImageSX($imgResource);
		$h = ImageSY($imgResource);
		if ($imgBlur = ImageCreateTrueColor($w, $h))
		{
			// Gaussian blur matrix:
			//	1	2	1
			//	2	4	2
			//	1	2	1

			// Move copies of the image around one pixel at the time and merge them with weight
			// according to the matrix. The same matrix is simply repeated for higher radii.
			for ($i = 0; $i < $iRadius; $i++)
			{
				ImageCopy     ($imgBlur			, $imgResource, 0, 0, 1, 1, $w - 1, $h - 1);            // up left
				ImageCopyMerge($imgBlur			, $imgResource, 1, 1, 0, 0, $w,     $h,     50.00000);  // down right
				ImageCopyMerge($imgBlur			, $imgResource, 0, 1, 1, 0, $w - 1, $h,     33.33333);  // down left
				ImageCopyMerge($imgBlur			, $imgResource, 1, 0, 0, 1, $w,     $h - 1, 25.00000);  // up right
				ImageCopyMerge($imgBlur			, $imgResource, 0, 0, 1, 0, $w - 1, $h,     33.33333);  // left
				ImageCopyMerge($imgBlur			, $imgResource, 1, 0, 0, 0, $w,     $h,     25.00000);  // right
				ImageCopyMerge($imgBlur			, $imgResource, 0, 0, 0, 1, $w,     $h - 1, 20.00000);  // up
				ImageCopyMerge($imgBlur			, $imgResource, 0, 1, 0, 0, $w,     $h,     16.666667); // down
				ImageCopyMerge($imgBlur			, $imgResource, 0, 0, 0, 0, $w,     $h,     50.000000); // center
				ImageCopy     ($imgResource	, $imgBlur		, 0, 0, 0, 0, $w,     $h);
			}
			return true;
		}
		return false;
	
	}
	
	function Colorize(&$imgResource, $iAmount = 25, $sColor = null)
	{
		$iAmount  = (is_numeric($iAmount) ? $iAmount : 25);
    
    Image::HexColorAllocate($imgResource, $sColor);

		$TargetPixel = array(
													'red' 	=> hexdec(substr($sColor,0,2)),
													'green' => hexdec(substr($sColor,2,2)),
													'blue' 	=> hexdec(substr($sColor,4,2))
												);
    for ($x = 0; $x < ImageSX($imgResource); $x++)
    {
	    for ($y = 0; $y < ImageSY($imgResource); $y++)
	    {
  	    $OriginalPixel = Image::PixelColor($imgResource, $x, $y);
        foreach ($TargetPixel as $key => $value)
	        $NewPixel[$key] = round(max(0, min(255, ($OriginalPixel[$key] * ((100 - $iAmount) / 100)) + ($TargetPixel[$key] * ($iAmount / 100)))));
				
				$newColor = ImageColorAllocate($imgResource, $NewPixel['red'], $NewPixel['green'], $NewPixel['blue']);
        ImageSetPixel($imgResource, $x, $y, $newColor);
    	}
    }
    return true;
	}
	
	function Sepia(&$imgResource, $iAmount = 50)
	{
		$iAmount  = (is_numeric($iAmount) ? $iAmount : 50);
		$sColor		= 'A28065';
		Image::HexColorAllocate($imgResource, $sColor);
		
		$TargetPixel = array(
											'red' 	=> hexdec(substr($sColor,0,2)),
											'green' => hexdec(substr($sColor,2,2)),
											'blue' 	=> hexdec(substr($sColor,4,2))
										);
		for ($x = 0; $x < ImageSX($imgResource); $x++)
    {
	    for ($y = 0; $y < ImageSY($imgResource); $y++)
	    {
  	    $OriginalPixel 	= Image::PixelColor($imgResource, $x, $y);
  	    $GrayPixel		 	= Image::GrayscalePixel($OriginalPixel);
  	    $SepiaAmount		= ((128 - abs($GrayPixel['red'] - 128)) / 128) * ($iAmount / 100);

        foreach ($TargetPixel as $key => $value)
	        $NewPixel[$key] = round(max(0, min(255, $GrayPixel[$key] * (1 - $SepiaAmount) + ($TargetPixel[$key] * $SepiaAmount))));
				
				$newColor = Image::ColorAllocateAlpha($imgResource, $NewPixel['red'], $NewPixel['green'], $NewPixel['blue'],$OriginalPixe['alpha']);
        ImageSetPixel($imgResource, $x, $y, $newColor);
    	}
    }
    return true;								
	}
	
	function Grayscale(&$imgResource, $iAmount = 100)
	{
		$iAmount  = (is_numeric($iAmount) ? $iAmount : 50);

    for ($x = 0; $x < ImageSX($imgResource); $x++)
    {
	    for ($y = 0; $y < ImageSY($imgResource); $y++)
	    {
  	    $OriginalPixel = Image::PixelColor($imgResource, $x, $y);
  	    $TargetPixel	 = Image::GrayscalePixel($OriginalPixel);
        foreach ($TargetPixel as $key => $value)
	        $NewPixel[$key] = round(max(0, min(255, ($OriginalPixel[$key] * ((100 - $iAmount) / 100)) + ($TargetPixel[$key] * ($iAmount / 100)))));
				
				$newColor = ImageColorAllocate($imgResource, $NewPixel['red'], $NewPixel['green'], $NewPixel['blue']);
        ImageSetPixel($imgResource, $x, $y, $newColor);
    	}
    }
    return true;
	}
	
	function Create($Width, $Height)
	{
		return ImageCreateTrueColor($Width, $Height);
	}
	
	function CreateFromFile($imgFile)
	{
			list($width, $height, $type, $attr) = @getimagesize($imgFile);
			switch($type)
			{
				case 1 : 
					return @ImageCreateFromGIF($imgFile);
					exit;
					break;
				case 2 : 
					return @ImageCreateFromJPEG($imgFile);
					exit;
					break;
				case 3 :
					return @ImageCreateFromPNG($imgFile);
					exit;
					break;
				default :
					exit;
			}
			return false;
	}
	
	function Process(&$imgResource, $batchArray, $cacheDirectory = IMAGE_CACHE_DIRECTORY)
	{
		$tmpFile = tempnam("/tmp","");
		imagepng($imgResource, $tmpFile);
		
		$imageID = md5_file($tmpFile);
		$processID = md5(serialize($batchArray));
		unlink($tmpFile);
		
		if(!file_exists($cacheDirectory.$imageID."_".$processID))
		{
			foreach($batchArray as $batchCmd)
			{
				$fctName = substr($batchCmd,0, strpos($batchCmd,':'));
				if(in_array(strtolower($fctName), get_class_methods('Image')))
				{
					$fctOptions = explode(',' , substr($batchCmd,strpos($batchCmd,':') + 1));
					Image::$fctName($imgResource,$fctOptions[0],$fctOptions[1],$fctOptions[2],$fctOptions[3],$fctOptions[4],$fctOptions[5],$fctOptions[6]);
				}
			}
			Image::Output($imgResource,IMAGE_OUTPUTMODE_FILE, IMAGE_OUTPUT_JPG, $cacheDirectory.$imageID."_".$processID);
		}
		else
			$imgResource = Image::CreateFromFile($cacheDirectory.$imageID."_".$processID);
	}
	
	function Gamma(&$imgResource, $iAmount = 1)
	{
		ImageGammaCorrect($imgResource, 1.0, $iAmount);
		return true;
	}
	
	function Contrast(&$imgResource, $iAmount = 0)
	{
		if($iAmount == 0)
			return true;
			
		$iAmount = max(-255, min(255,$iAmount));
		
		if($iAmount > 0) 
			$scaling = 1 + ($iAmount / 255);
		else
			$scaling = (255 - abs($iAmount)) / 255;
		
		for ($x = 0; $x < ImageSX($imgResource); $x++)
		{
      for ($y = 0; $y < ImageSY($imgResource); $y++)
      {
          $OriginalPixel = Image::PixelColor($imgResource, $x, $y);
          foreach ($OriginalPixel as $key => $value)
          {
              $NewPixel[$key] = min(255, max(0, round($OriginalPixel[$key] * $scaling)));
          }
          $newColor = ImageColorAllocate($imgResource, $NewPixel['red'], $NewPixel['green'], $NewPixel['blue']);
          ImageSetPixel($imgResource, $x, $y, $newColor);
     	}
		}
		return true;	
	}
	
	function Brightness(&$imgResource, $iAmount = 0)
	{
		if($iAmount == 0)
			return true;
			
		$iAmount = max(-255, min(255,$iAmount));
		
		$scaling 		= (255 - abs($iAmount)) / 255;
		$baseamount = (($iAmount > 0) ? $iAmount : 0);
		
		for ($x = 0; $x < ImageSX($imgResource); $x++)
		{
	    for ($y = 0; $y < ImageSY($imgResource); $y++)
	    {
	        $OriginalPixel = Image::PixelColor($imgResource, $x, $y);
	        foreach ($OriginalPixel as $key => $value)
	        {
	            $NewPixel[$key] = round($baseamount + ($OriginalPixel[$key] * $scaling));
	        }
	        $newColor = ImageColorAllocate($imgResource, $NewPixel['red'], $NewPixel['green'], $NewPixel['blue']);
	        ImageSetPixel($imgResource, $x, $y, $newColor);
	    }
		}
    return true;
	}
	
	function Resize(&$imgResource, $iWidth, $iHeight, $iConstraint = IMAGE_RESIZE_PROPORTIONAL)
	{
		$srcWidth = imagesx($imgResource);
		$srcHeight = imagesy($imgResource);
		
		if(($iWidth == $srcWidth) && ($iHeight == $srcHeight))
			return true;
		
		
		if($iConstraint != false)
		{
			$ratioX = $iWidth / $srcWidth;
			$ratioY = $iHeight / $srcHeight;

			if($ratioX < $ratioY)
			{
				$iHeight 	= round($srcHeight * $ratioX);
				$iWidth 	= round($srcWidth * $ratioX);
			}
			else
			{
				$iWidth 	= $srcWidth * $ratioY;
				$iHeight 	= $srcHeight * $ratioY;
			}
		}
		
		$imgProcessed = ImageCreateTrueColor($iWidth,$iHeight);
		ImageCopyResampled($imgProcessed, $imgResource, 0, 0, 0, 0, $iWidth, $iHeight, $srcWidth, $srcHeight);
		$imgResource = $imgProcessed;
	}
	
	function Crop(&$imgResource, $iWidth, $iHeight, $iTop = 0 , $iLeft = 0 , $iAlignement = IMAGE_ALIGN_NONE)
	{
		
		$srcWidth 		= ImageSX($imgResource);
		$srcHeight 		= ImageSY($imgResource);
		$destWidth 		= $iWidth;
		$destHeight 	= $iHeight;
		
		// Horizontal Alignement
		if(( IMAGE_ALIGN_LEFT 	& $iAlignement ) == IMAGE_ALIGN_LEFT )
			$iLeft = 0;
		if(( IMAGE_ALIGN_RIGHT 	& $iAlignement ) == IMAGE_ALIGN_RIGHT )
			$iLeft = ImageSX($imgResource) - $iWidth;
		if(( IMAGE_ALIGN_CENTER & $iAlignement ) == IMAGE_ALIGN_CENTER )
			$iLeft = (ImageSX($imgResource) - $iWidth) /2;
		
		// Vertical Alignement
		if(( IMAGE_ALIGN_TOP 		& $iAlignement ) == IMAGE_ALIGN_TOP )
			$iTop = 0;
		if(( IMAGE_ALIGN_BOTTOM & $iAlignement ) == IMAGE_ALIGN_BOTTOM )
			$iTop = ImageSY($imgResource) - $iHeight;
		if(( IMAGE_ALIGN_MIDDLE & $iAlignement ) == IMAGE_ALIGN_MIDDLE )
			$iTop = (ImageSY($imgResource) - $iWidth) /2;
		
		
		$imgProcessed = ImageCreateTrueColor($iWidth,$iHeight);
		
		if($srcWidth > $srcHeight)
		{
			if($destHeight > ($srcHeight - $iTop))
			{
				$destHeight 	=	$srcHeight - $iTop;
				$destWidth 		=	floor($destHeight * ($iWidth / $iHeight));
			}
		}
		else
		{
			if($destWidth > ($srcWidth - $iLeft))
			{
				$destWidth 		=	$srcWidth - $iLeft;
				$destHeight 	=	floor($destWidth / ($iWidth / $iHeight));
			}
		}

		ImageCopyResampled($imgProcessed, $imgResource, 0, 0, $iLeft, $iTop, $iWidth, $iHeight, $destWidth, $destHeight);
		$imgResource = $imgProcessed;		
		
	}
	
	function Mask(&$imgResource, $maskFileName)
	{
		$imgMask = Image::CreateFromFile($maskFileName);
		$imgMaskResized = imagecreatetruecolor(ImageSX($imgResource),ImageSY($imgResource));
		ImageCopyResampled($imgMaskResized , $imgMask,0,0,0,0, ImageSX($imgResource),ImageSY($imgResource),ImageSX($imgMask),ImageSY($imgMask));
		
		$imgMaskBlending = imagecreatetruecolor(ImageSX($imgResource),ImageSY($imgResource));
		$bgColor = ImageColorAllocate($imgMaskBlending, 0,0,0);
		ImageFilledRectangle($imgMaskBlending,0,0,ImageSX($imgMaskBlending),ImageSY($imgMaskBlending),$bgColor);
		ImageAlphaBlending($imgMaskBlending,false);
		ImageSaveAlpha($imgMaskBlending, true);
		
		for( $x=0; $x<ImageSX($imgResource);$x++)
		{
			for($y=0;$y<ImageSY($imgResource);$y++)
			{
				$SourcePixelColor = Image::PixelColor($imgResource, $x, $y);
				$MaskPixelColor   = Image::GrayscalePixel(Image::PixelColor($imgMask, $x, $y));
				$MaskAlpha				= 127 - (floor($MaskPixelColor['red'] / 2 ) * (1 - ($SourcePixelColor['alpha'] / 127)));
				$newPixelColor		= Image::ColorAllocateAlpha($imgMaskBlending, $SourcePixelColor['red'],$SourcePixelColor['green'],$SourcePixelColor['blue'],$MaskAlpha);
				ImageSetPixel($imgMaskBlending,$x, $y , $newPixelColor);
			}
		}
		ImageAlphaBlending($imgResource, false);
		ImageSaveAlpha($imgResource, true);
		ImageCopy($imgResource, $imgMaskBlending, 0,0,0,0, ImageSX($imgMaskBlending), ImageSY($imgMaskBlending));
		ImageDestroy($imgMaskBlending);
		ImageDestroy($imgMaskResized);
	}
	
	function Background(&$imgResource, $hexColor)
	{
		$imgTmp = ImageCreateTrueColor(ImageSX($imgResource),ImageSY($imgResource));
		$bgColor = Image::HexColorAllocate($imgResource, $hexColor);
		ImageFilledRectangle($imgTmp, 0, 0, ImageSX($imgTmp),ImageSY($imgTmp), $bgColor);
		ImageCopy($imgTmp, $imgResource, 0,0,0,0, ImageSX($imgTmp),ImageSY($imgTmp));
		ImageAlphaBlending($imgResource, true);
		ImageSaveAlpha($imgResource, false);
		ImageColorTransparent($imgResource, -1);
		ImageCopy($imgResource, $imgTmp, 0,0, 0,0, ImageSX($imgResource),ImageSY($imgResource));
		ImageDestroy($imgTmp);
		return true;
	}
	
	function HexColorAllocate(&$imgResource, $hexColor)
	{
		preg_match_all('`(.{2})`',$hexColor,$matches);
		$red 		= hexdec($matches[0][0]);
		$green 	= hexdec($matches[0][1]);
		$blue 	= hexdec($matches[0][2]);
		return Image::ColorAllocateAlpha($imgResource, $red, $green, $blue, 0);
	}
	
	function ColorAllocateAlpha(&$img, $redValue, $greenValue, $blueValue, $alphaValue)
	{
		if($alphaValue !== false)
			return ImageColorAllocateAlpha($img, $redValue, $greenValue, $blueValue, intval($alphaValue));
    else
			return ImageColorAllocate($img, $redValue, $greenValue, $blueValue);
	}
	
	function PixelColor(&$img, $x, $y)
	{
		return ImageColorsForIndex($img, ImageColorAt($img, $x, $y));
	}
	
	function GrayscaleValue($redValue, $greenValue, $blueValue)
	{
		return round(($redValue * 0.30) + ($greenValue * 0.59) + ($blueValue * 0.11));	
	}
	
	function GrayscalePixel($srcPixel)
	{
		$grayValue = Image::GrayscaleValue($srcPixel['red'],$srcPixel['green'],$srcPixel['blue']);
		return array('red' => $grayValue, 'green' => $grayValue, 'blue' => $grayValue);
	}
	
	function Output(&$imgResource, $iOutputMode = IMAGE_OUTPUTMODE_HTTP , $imgFormat = IMAGE_OUTPUT_PNG , $sFileName = null)
	{
		switch($iOutputMode)
		{
			case IMAGE_OUTPUTMODE_BASE64 :
				ob_start();
					imagepng(&$imgResource);
					$strImage= ob_get_contents();
				ob_end_clean();
				return sprintf("data:image/png;base64,%s",base64_encode($strImage));
				break;
			case IMAGE_OUTPUTMODE_FILE :
				if(!is_null($sFileName))
				{
					if($imgFormat == IMAGE_OUTPUT_PNG)
						@imagepng($imgResource,$sFileName);
					else if($imgFormat == IMAGE_OUTPUT_GIF)
						@imagegif($imgResource,$sFileName);
					else if($imgFormat == IMAGE_OUTPUT_JPG)
						@imagejpeg($imgResource,$sFileName);
					return true;
				}
				else
				{
					trigger_error("Missing parameters 3.", E_USER_ERROR);
					return false;
				}
				break;
			case IMAGE_OUTPUTMODE_FORCEDOWNLOAD :
				header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
				header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
				header("Cache-Control: no-store, no-cache, must-revalidate");
				header("Cache-Control: post-check=0, pre-check=0", false);
				header("Cache-Control: max-age=10000000, s-maxage=1000000, proxy-revalidate, must-revalidate");
				header("Pragma: no-cache");
				if($imgFormat == IMAGE_OUTPUT_PNG)
				{
					header('Content-type: image/png');
					header('Content-Disposition: attachment; filename="tmpimage.png"');
					imagepng($imgResource);
				}
				else if ($imgFormat == IMAGE_OUTPUT_GIF)
				{
					header('Content-type: image/gif');
					header('Content-Disposition: attachment; filename="tmpimage.gif"');
					imagegif($imgResource);
				}
				else if ($imgFormat == IMAGE_OUTPUT_GIF)
				{
					header('Content-type: image/jpeg');
					header('Content-Disposition: attachment; filename="tmpimage.jpg"');
					imagejpeg($imgResource);
				}
				return true;
				break;
			default:
			case IMAGE_OUTPUTMODE_HTTP :
				header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
				header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
				header("Cache-Control: no-store, no-cache, must-revalidate");
				header("Cache-Control: post-check=0, pre-check=0", false);
				header("Cache-Control: max-age=10000000, s-maxage=1000000, proxy-revalidate, must-revalidate");
				header("Pragma: no-cache");
				if($imgFormat == IMAGE_OUTPUT_PNG)
				{
					header('Content-type: image/png');
					imagepng($imgResource);
				}
				else if($imgFormat == IMAGE_OUTPUT_GIF)
				{
					header('Content-type: image/gif');
					imagegif($imgResource);
				}
				if($imgFormat == IMAGE_OUTPUT_JPG)
				{
					header('Content-type: image/jpg');
					imagejpeg($imgResource);
				}
				return true;
				break;
		} 
	}
}

//# Test 
//
//$img = ImageCreateFromJPEG("images/Maléfique.jpg");
//
//foreach($_GET['filter'] as $filter=>$value)
//{
////	list($filter,$value) = explode(':',$v);
//	if(in_array($filter,get_class_methods("Image")))
//		Image::$filter($img,$value);
//	
//}
//print_r($_GET['filter']);
////Image::Gamma($img,20);
//$status = Image::Output($img,IMAGE_OUTPUTMODE_BASE64);
//echo "<br/><img src='$status' />";
?>