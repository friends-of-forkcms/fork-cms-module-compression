<?php
declare(strict_types=1);

namespace Backend\Modules\Compression\Domain\CompressionSetting;

use Backend\Modules\Compression\Domain\CompressionSetting\Command\UpdateCompressionSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CompressionSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('folders', HiddenType::class, ['label' => 'lbl.Folders']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UpdateCompressionSettings::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'compression_settings';
    }
}
