<?php

/*
 * This file is part of CommSy.
 *
 * (c) Matthias Finck, Dirk Fust, Oliver Hankel, Iver Jackewitz, Michael Janneck,
 * Martti Jeenicke, Detlev Krause, Irina L. Marinescu, Timo Nolte, Bernd Pape,
 * Edouard Simon, Monique Strauss, Jose Mauel Gonzalez Vazquez, Johannes Schultze
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace App\Form\Type;

use App\Form\Type\Custom\CategoryMappingType;
use App\Form\Type\Custom\DateTimeSelectType;
use App\Form\Type\Custom\HashtagMappingType;
use App\Form\Type\Event\AddBibliographicFieldListener;
use App\Form\Type\Event\AddEtherpadFormListener;
use cs_context_item;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class MaterialType extends AbstractType
{
    public function __construct(private AddEtherpadFormListener $etherpadFormListener)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, ['constraints' => [new NotBlank()], 'label' => 'title', 'attr' => ['placeholder' => $options['placeholderText'], 'class' => 'uk-form-width-medium cs-form-title'], 'translation_domain' => 'material'])
            ->add('permission', CheckboxType::class, ['label' => 'permission', 'required' => false, 'label_attr' => ['class' => 'uk-form-label']])
            ->add('hidden', CheckboxType::class, ['label' => 'hidden', 'required' => false])
            ->add('hiddendate', DateTimeSelectType::class, ['label' => 'hidden until'])
            ->addEventSubscriber($this->etherpadFormListener)
            ->add('biblio_select', ChoiceType::class, ['choices' => ['plain' => 'BiblioPlainType', 'book' => 'BiblioBookType', 'collection' => 'BiblioCollectionType', 'article' => 'BiblioArticleType', 'journal' => 'BiblioJournalType', 'chapter' => 'BiblioChapterType', 'newspaper' => 'BiblioNewspaperType', 'thesis' => 'BiblioThesisType', 'manuscript' => 'BiblioManuscriptType', 'website' => 'BiblioWebsiteType', 'document management' => 'BiblioDocManagementType', 'picture' => 'BiblioPictureType'], 'label' => 'bib reference', 'choice_translation_domain' => true, 'required' => false])
            ->addEventSubscriber(new AddBibliographicFieldListener())
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $material = $event->getData();
                $form = $event->getForm();
                $formOptions = $form->getConfig()->getOptions();

                if ($material['external_viewer_enabled']) {
                    $form->add('external_viewer', TextType::class, [
                        'required' => false,
                    ]);
                }

                if ($material['draft']) {
                    /** @var cs_context_item $room */
                    $room = $formOptions['room'];

                    if ($room->withBuzzwords()) {
                        $hashtagOptions = array_merge($formOptions['hashtagMappingOptions'], [
                            'assignment_is_mandatory' => $room->isBuzzwordMandatory(),
                        ]);
                        $form->add('hashtag_mapping', HashtagMappingType::class, $hashtagOptions);
                    }

                    if ($room->withTags()) {
                        $categoryOptions = array_merge($formOptions['categoryMappingOptions'], [
                            'assignment_is_mandatory' => $room->isTagMandatory(),
                        ]);
                        $form->add('category_mapping', CategoryMappingType::class, $categoryOptions);
                    }
                }
            })
            ->add('license_id', ChoiceType::class, ['required' => false, 'expanded' => false, 'multiple' => false, 'choices' => $options['licenses'], 'translation_domain' => 'material'])
            ->add('save', SubmitType::class, ['attr' => ['class' => 'uk-button-primary'], 'label' => 'save'])
            ->add('cancel', SubmitType::class, ['attr' => ['formnovalidate' => ''], 'label' => 'cancel'])
        ;
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['placeholderText', 'hashtagMappingOptions', 'categoryMappingOptions', 'licenses', 'room']);

        $resolver->setDefaults(['translation_domain' => 'form']);

        $resolver->setAllowedTypes('room', 'cs_context_item');
    }
}
