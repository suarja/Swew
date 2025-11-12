<?php

namespace App\Controller\Admin;

use App\Entity\Lesson;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class LessonCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Lesson::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('course'),
            TextField::new('title'),
            SlugField::new('slug')->setTargetFieldName('title'),
            IntegerField::new('sequencePosition')->setHelp('Ordering inside the course'),
            TextareaField::new('summary')->hideOnIndex(),
            TextareaField::new('content')->hideOnIndex(),
            AssociationField::new('assignments')->onlyOnDetail(),
            DateTimeField::new('updatedAt')->hideOnForm(),
        ];
    }
}
