#!/usr/bin/env php
<?php

require 'vendor/autoload.php';

use \Symfony\Component\Console\Application;
use \Symfony\Component\Console\Command\Command;

$app = new Application();

class resize extends Command
{
    protected static $defaultName = 'resize';

    protected function configure () {
        // args: width height inputImg outputImg


        $this
            ->setDescription('Resize image')
            ->setHelp('Resize PNG or JPEG and save result to new file')
            ->addArgument(
                'width',
                \Symfony\Component\Console\Input\InputArgument::REQUIRED,
                'Output image width.')
            ->addArgument(
                'height',
                \Symfony\Component\Console\Input\InputArgument::REQUIRED,
                'Output image height.')
            ->addArgument(
                    'input',
                    \Symfony\Component\Console\Input\InputArgument::REQUIRED,
                    'Input image file.')
            ->addArgument(
                'output',
                \Symfony\Component\Console\Input\InputArgument::REQUIRED,
                'Output image file.');
    }

    public function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {
        $width = $input->getArgument('width');
        $height = $input->getArgument('height');
        $inputFile = $input->getArgument('input');
        $outputFile = $input->getArgument('output');

        $ext = pathinfo($inputFile)['extension'];

        if($ext === 'jpg' | $ext === 'jpeg')
        {
            $image = imagecreatefromjpeg($inputFile);
            $outputImg = imagecreatetruecolor($width, $height);
            $origW = imagesx($image);
            $origH = imagesy($image);

            imagecopyresized($outputImg, $image, 0, 0,0,0, $width, $height, $origW, $origH);
            imagejpeg($outputImg, $outputFile);
        }

        if($ext === 'png')
        {
            $image = imagecreatefrompng($inputFile);
            $outputImg = imagecreatetruecolor($width, $height);
            $origW = imagesx($image);
            $origH = imagesy($image);

            imagecopyresized($outputImg, $image, 0, 0,0,0, $width, $height, $origW, $origH);
            imagepng($outputImg, $outputFile);
        }
    }
}

class grayscale extends Command
{
    protected static $defaultName = 'grayscale';

    protected function configure () {
        // args: width height inputImg outputImg


        $this
            ->setDescription('Put a grayscale filter on an image')
            ->addArgument(
                'input',
                \Symfony\Component\Console\Input\InputArgument::REQUIRED,
                'Input image file.')
            ->addArgument(
                'output',
                \Symfony\Component\Console\Input\InputArgument::REQUIRED,
                'Output image file.');
    }

    public function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {
        $inputFile = $input->getArgument('input');
        $outputFile = $input->getArgument('output');

        $ext = pathinfo($inputFile)['extension'];

        if($ext === 'jpg' | $ext === 'jpeg')
        {
            $image = imagecreatefromjpeg($inputFile);

            imagefilter($image, IMG_FILTER_GRAYSCALE);
            imagejpeg($image, $outputFile);
        }

        if($ext === 'png')
        {
            $image = imagecreatefrompng($inputFile);

            imagefilter($image, IMG_FILTER_GRAYSCALE);
            imagepng($image, $outputFile);
        }
    }
}

function drawStar ( $width, $color, $bgColor, $points, $radius ) {
    $img = imagecreatetruecolor($width, $width);
    $starColor = imagecolorallocate($img, $color[0], $color[1], $color[2]);
    $bgColor = imagecolorallocate($img, $bgColor[0], $bgColor[1], $bgColor[2]);

    imagefilledrectangle($img, 0, 0, $width, $width, $bgColor);

    $cx = $width/2;
    $cy = $width/2;
    $r = $width/2;

    $pointArray = [];

    for($i=0; $i<$points; $i++){

        // for each point

        $angle = deg2rad((360 / $points) * $i );

        $x = $cx + ($r * sin($angle));
        array_push($pointArray, $x);
        $y = $cy + ($r * cos($angle));
        array_push($pointArray, $y);

        $angle += deg2rad(((360 / $points)) / 2);

        $x = $cx + $r * sin($angle) * $radius;
        array_push($pointArray, $x);
        $y = $cy + $r * cos($angle) * $radius;
        array_push($pointArray, $y);
    }

    // x = cx + r * sin(angle)
    // y = cy + r * cos(angle)
    print_r($pointArray);

    imagefilledpolygon($img, $pointArray, $points*2, $starColor);
    imagejpeg($img, 'star.jpg');

}
drawStar(500, [245, 166, 35], [255, 255, 255], 14, 0.6);

$app->add(new resize());
$app->add(new grayscale());

$app->run();

