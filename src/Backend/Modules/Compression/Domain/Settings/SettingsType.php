<?php
declare(strict_types=1);

namespace Backend\Modules\Compression\Domain\Settings;

use Backend\Modules\Compression\Domain\Settings\Command\SaveSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('apiKey', TextType::class, ['required' => true, 'label' => 'lbl.ApiKey']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SaveSettings::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'settings';
    }
}
