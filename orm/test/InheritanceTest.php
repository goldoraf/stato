<?php

require_once dirname(__FILE__) . '/../../test/TestsHelper.php';

class InheritanceTest extends ActiveTestCase
{
    public $fixtures = array('foods', 'ingredients', 'foods_ingredients', 'recipes', 'chiefs');
    
    public function test_create()
    {
        $pizza = new Pizza(array('name' => 'Capriciosa'));
        $pizza->save();
        $this->assertEquals('pizza', $pizza->type);
        $pizza->reload();
        $this->assertEquals('pizza', $pizza->type);
    }
    
    public function test_retrieve_right_object()
    {
        $pizza = Food::$objects->get(1);
        $this->assertEquals('Pizza', get_class($pizza));
        $pizza = Pizza::$objects->get(2);
        $this->assertEquals('Pizza', get_class($pizza));
        $burger = Food::$objects->get(3);
        $this->assertEquals('Burger', get_class($burger));
        $burger = Burger::$objects->get(4);
        $this->assertEquals('Burger', get_class($burger));
    }
    
    public function test_super_inheritance()
    {
        $maxi = new MaxiBurger(array('name' => 'GiantWhopper'));
        $maxi->save();
        $this->assertEquals('maxi_burger', $maxi->type);
        $maxi2 = MaxiBurger::$objects->get($maxi->id);
        $this->assertEquals('maxi_burger', $maxi2->type);
        $this->assertEquals('MaxiBurger', get_class($maxi2));
    }
    
    public function test_associations()
    {
        $pizza = Pizza::$objects->get(1);
        $this->assertEquals('Tomatoes', $pizza->ingredients->first()->name);
        $this->assertEquals('Mario', $pizza->chief->name);
        $this->assertEquals('Sicilian way', $pizza->recipes->first()->name);
        $tomatoes = Ingredient::$objects->get(1);
        $margherita = $tomatoes->foods->first();
        $this->assertEquals('Margherita', $margherita->name);
        $this->assertEquals('Pizza', get_class($margherita));
        $mario = Chief::$objects->get(1);
        $margherita = $mario->foods->first();
        $this->assertEquals('Margherita', $margherita->name);
        $this->assertEquals('Pizza', get_class($margherita));
        $sicilian = Recipe::$objects->get(1);
        $margherita = $sicilian->food->target();
        $this->assertEquals('Margherita', $margherita->name);
        $this->assertEquals('Pizza', get_class($margherita));
    }
}